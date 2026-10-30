<?php

namespace App\Services\Dte;

use App\DTOs\BoletaData;
use App\DTOs\CaratulaData;
use App\DTOs\DetalleItemData;
use App\DTOs\EmisorData;
use App\DTOs\ReceptorData;
use App\Exceptions\DteException;
use App\Jobs\Dte\SendBoletaToSiiJob;
use App\Models\Dte\AuditDte;
use App\Models\Dte\Certificado;
use App\Models\Dte\ConfiguracionFiscal;
use App\Models\Dte\DocumentoTributario;
use App\Models\Dte\DocumentoTributarioDetalle;
use App\Models\OrdenServicio;
use App\Services\Dte\Folio\CafManager;
use App\Services\Dte\Folio\FolioAllocator;
use App\Services\Dte\Tax\TributaryCalculator;
use App\Services\Dte\Xml\DteXmlBuilder;
use App\Services\Dte\Xml\TedGenerator;
use App\Services\Dte\Xml\XmlSigner;
use Illuminate\Support\Facades\DB;

class DteService
{
    private TributaryCalculator $calculator;
    private FolioAllocator $folioAllocator;
    private CafManager $cafManager;
    private DteXmlBuilder $xmlBuilder;
    private TedGenerator $tedGenerator;
    private XmlSigner $xmlSigner;

    public function __construct(
        TributaryCalculator $calculator,
        FolioAllocator $folioAllocator,
        CafManager $cafManager,
        DteXmlBuilder $xmlBuilder,
        TedGenerator $tedGenerator,
        XmlSigner $xmlSigner
    ) {
        $this->calculator = $calculator;
        $this->folioAllocator = $folioAllocator;
        $this->cafManager = $cafManager;
        $this->xmlBuilder = $xmlBuilder;
        $this->tedGenerator = $tedGenerator;
        $this->xmlSigner = $xmlSigner;
    }

    public function emitirBoleta(int $concessionId, OrdenServicio $orden, array $items): DocumentoTributario
    {
        $config = ConfiguracionFiscal::where('id_concession', $concessionId)
            ->activa()
            ->firstOrFail();

        $certificado = Certificado::where('id_concession', $concessionId)
            ->activo()
            ->vigente()
            ->firstOrFail();

        $idempotencyKey = hash('sha256', "{$concessionId}:39:{$orden->id}:" . now()->timestamp);

        $existente = DocumentoTributario::where('idempotency_key', $idempotencyKey)->first();
        if ($existente) {
            return $existente;
        }

        return DB::transaction(function () use ($concessionId, $orden, $items, $config, $certificado, $idempotencyKey) {
            $detalles = $this->construirDetalles($items);
            $impuestos = $this->calculator->calcularDetallesBoleta($detalles);

            $folioData = $this->folioAllocator->asignar($concessionId, 39);
            $folio = $folioData['folio'];
            $caf = $folioData['caf'];

            $this->cafManager->cargar($caf);

            $emisor = EmisorData::fromConfiguracion($config);
            $receptor = $this->construirReceptor($orden);

            $boletaData = new BoletaData([
                'tipo_documento' => 39,
                'folio' => $folio,
                'fecha_emision' => date('Y-m-d'),
                'ind_servicio' => 3,
                'emisor' => $emisor,
                'receptor' => $receptor,
                'detalles' => $detalles,
                'monto_neto' => $impuestos['neto'],
                'monto_iva' => $impuestos['iva'],
                'monto_exento' => $impuestos['exento'],
                'monto_total' => $impuestos['total'],
            ]);

            $dteDoc = $this->xmlBuilder->construirBoleta39($boletaData);

            $tedXml = $this->tedGenerator->generar($boletaData, $this->cafManager);
            $this->tedGenerator->insertarEnDocumento($dteDoc, $tedXml, $boletaData->getDocumentoId());

            $certData = $this->xmlSigner->cargarCertificado(
                $certificado->archivo_completo,
                $certificado->password_descifrada
            );

            $this->xmlSigner->firmar(
                $dteDoc,
                $boletaData->getDocumentoId(),
                $certData['private_key'],
                $certData['cert_pem']
            );

            $caratula = new CaratulaData([
                'rut_emisor' => $config->rut_empresa,
                'rut_envia' => $certData['rut_titular'],
                'fecha_resolucion' => $config->fecha_resolucion->format('Y-m-d'),
                'numero_resolucion' => $config->numero_resolucion,
                'sub_totales' => [39 => 1],
            ]);

            $envioDoc = $this->xmlBuilder->construirEnvioBoleta($dteDoc, $caratula);

            $this->xmlSigner->firmar(
                $envioDoc,
                'DTE_SetDoc',
                $certData['private_key'],
                $certData['cert_pem']
            );

            $documento = DocumentoTributario::create([
                'id_concession' => $concessionId,
                'tipo_documento' => 39,
                'folio' => $folio,
                'fecha_emision' => date('Y-m-d'),
                'rut_emisor' => $config->rut_empresa,
                'rut_receptor' => $receptor->rutRecep,
                'razon_social_receptor' => $receptor->razonSocialRecep,
                'monto_neto' => $impuestos['neto'],
                'monto_iva' => $impuestos['iva'],
                'tasa_iva' => $this->calculator->getTasaIva(),
                'monto_exento' => $impuestos['exento'],
                'monto_total' => $impuestos['total'],
                'xml_documento' => $dteDoc->saveXML(),
                'xml_sobre' => $envioDoc->saveXML(),
                'ted_xml' => $tedXml,
                'estado' => 'emitido',
                'orden_servicio_id' => $orden->id,
                'idempotency_key' => $idempotencyKey,
                'caf_folio_id' => $caf->id,
            ]);

            foreach ($detalles as $detalle) {
                DocumentoTributarioDetalle::create([
                    'documento_id' => $documento->id,
                    'nro_linea' => $detalle->nroLinea,
                    'nombre_item' => $detalle->nombreItem,
                    'descripcion' => $detalle->descripcion,
                    'cantidad' => $detalle->cantidad,
                    'unidad_medida' => $detalle->unidadMedida,
                    'precio_unitario' => $detalle->precioUnitario,
                    'descuento_monto' => $detalle->descuentoMonto,
                    'monto_item' => $detalle->montoItem,
                    'producto_id' => $detalle->productoId,
                    'servicio_id' => $detalle->servicioId,
                ]);
            }

            AuditDte::registrar(
                $concessionId,
                $documento->id,
                'boleta_emitida',
                [
                    'folio' => $folio,
                    'monto_total' => $impuestos['total'],
                    'orden_servicio_id' => $orden->id,
                ],
                null,
                'emitido'
            );

            SendBoletaToSiiJob::dispatch($documento->id);

            return $documento;
        });
    }

    private function construirDetalles(array $items): array
    {
        $detalles = [];
        foreach ($items as $i => $item) {
            $monto = $this->calculator->calcularMontoItem(
                (int) $item['precio_unitario'],
                (float) ($item['cantidad'] ?? 1),
                (int) ($item['descuento'] ?? 0)
            );

            $detalles[] = new DetalleItemData([
                'nro_linea' => $i + 1,
                'nombre_item' => $item['nombre'],
                'descripcion' => $item['descripcion'] ?? null,
                'cantidad' => $item['cantidad'] ?? 1,
                'unidad_medida' => $item['unidad_medida'] ?? null,
                'precio_unitario' => $item['precio_unitario'],
                'descuento_monto' => $item['descuento'] ?? 0,
                'monto_item' => $monto,
                'producto_id' => $item['producto_id'] ?? null,
                'servicio_id' => $item['servicio_id'] ?? null,
            ]);
        }

        return $detalles;
    }

    private function construirReceptor(OrdenServicio $orden): ReceptorData
    {
        $cliente = $orden->cliente;

        if (!$cliente || !$cliente->rut) {
            return ReceptorData::anonimo();
        }

        return new ReceptorData([
            'rut' => $cliente->rut,
            'razon_social' => trim($cliente->nombre . ' ' . ($cliente->apellido ?? '')),
            'direccion' => $cliente->direccion,
            'comuna' => $cliente->comuna ?? null,
            'ciudad' => $cliente->ciudad ?? null,
        ]);
    }
}
