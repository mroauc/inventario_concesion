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
use App\Models\Dte\EnvioSii;
use App\Models\OrdenServicio;
use App\Services\Dte\Folio\CafManager;
use App\Services\Dte\Folio\FolioAllocator;
use App\Services\Dte\Sii\SiiAuthClient;
use App\Services\Dte\Sii\SiiBoletaClient;
use App\Services\Dte\Tax\TributaryCalculator;
use App\Services\Dte\Xml\DteXmlBuilder;
use App\Services\Dte\Xml\TedGenerator;
use App\Services\Dte\Xml\XmlSigner;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class DteService
{
    private TributaryCalculator $calculator;
    private FolioAllocator $folioAllocator;
    private CafManager $cafManager;
    private DteXmlBuilder $xmlBuilder;
    private TedGenerator $tedGenerator;
    private XmlSigner $xmlSigner;
    private SiiAuthClient $authClient;
    private SiiBoletaClient $boletaClient;

    public function __construct(
        TributaryCalculator $calculator,
        FolioAllocator $folioAllocator,
        CafManager $cafManager,
        DteXmlBuilder $xmlBuilder,
        TedGenerator $tedGenerator,
        XmlSigner $xmlSigner,
        SiiAuthClient $authClient,
        SiiBoletaClient $boletaClient
    ) {
        $this->calculator = $calculator;
        $this->folioAllocator = $folioAllocator;
        $this->cafManager = $cafManager;
        $this->xmlBuilder = $xmlBuilder;
        $this->tedGenerator = $tedGenerator;
        $this->xmlSigner = $xmlSigner;
        $this->authClient = $authClient;
        $this->boletaClient = $boletaClient;
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

        $receptor = $this->construirReceptor($orden);

        $documento = $this->crearDocumentoTributario(
            $concessionId,
            $items,
            $receptor,
            $config,
            $certificado,
            $idempotencyKey,
            $orden->id
        );

        SendBoletaToSiiJob::dispatch($documento->id);

        return $documento;
    }

    public function emitirBoletaRapida(int $concessionId, array $items, ?array $datosReceptor = null): DocumentoTributario
    {
        $config = ConfiguracionFiscal::where('id_concession', $concessionId)
            ->activa()
            ->firstOrFail();

        $certificado = Certificado::where('id_concession', $concessionId)
            ->activo()
            ->vigente()
            ->firstOrFail();

        $idempotencyKey = hash('sha256', "{$concessionId}:39:rapida:" . now()->timestamp . ':' . md5(json_encode($items)));

        $existente = DocumentoTributario::where('idempotency_key', $idempotencyKey)->first();
        if ($existente) {
            return $existente;
        }

        $receptor = $this->construirReceptorDesdeArray($datosReceptor);

        $documento = $this->crearDocumentoTributario(
            $concessionId,
            $items,
            $receptor,
            $config,
            $certificado,
            $idempotencyKey,
            null
        );

        $this->enviarSincrono($documento, $config, $certificado);

        return $documento->fresh();
    }

    public function enviarSincrono(
        DocumentoTributario $documento,
        ?ConfiguracionFiscal $config = null,
        ?Certificado $certificado = null
    ): array {
        $config = $config ?? ConfiguracionFiscal::where('id_concession', $documento->id_concession)
            ->activa()
            ->firstOrFail();

        $certificado = $certificado ?? Certificado::where('id_concession', $documento->id_concession)
            ->activo()
            ->vigente()
            ->firstOrFail();

        $token = $this->authClient->obtenerToken($config, $certificado);

        $certData = $this->xmlSigner->cargarCertificado(
            $certificado->archivo_completo,
            $certificado->password_descifrada
        );

        $resultado = $this->boletaClient->enviar(
            $config,
            $token,
            $certData['rut_titular'],
            $documento->xml_sobre
        );

        EnvioSii::create([
            'id_concession'    => $documento->id_concession,
            'tipo_envio'       => 'boleta',
            'documento_id'     => $documento->id,
            'xml_enviado_hash' => hash('sha256', $documento->xml_sobre),
            'respuesta_raw'    => json_encode($resultado),
            'track_id'         => $resultado['track_id'],
            'status_code'      => $resultado['status_code'],
            'http_status'      => 200,
            'intentos'         => 1,
            'ambiente'         => $config->ambiente,
        ]);

        if ($resultado['exitoso'] && $resultado['track_id']) {
            $estadoAnterior = $documento->estado;
            $documento->update([
                'estado'          => 'enviado',
                'track_id'        => $resultado['track_id'],
                'envio_intentos'  => 1,
                'ultimo_envio_at' => now(),
            ]);

            AuditDte::registrar(
                $documento->id_concession,
                $documento->id,
                'envio_sii_sincrono_exitoso',
                [
                    'track_id'    => $resultado['track_id'],
                    'status_code' => $resultado['status_code'],
                ],
                $estadoAnterior,
                'enviado'
            );

            Log::info('Boleta enviada al SII sincronamente', [
                'documento_id' => $documento->id,
                'track_id'     => $resultado['track_id'],
            ]);
        } else {
            $documento->update([
                'envio_intentos'  => 1,
                'ultimo_envio_at' => now(),
                'respuesta_sii'   => $resultado['glosa'],
            ]);

            AuditDte::registrar(
                $documento->id_concession,
                $documento->id,
                'envio_sii_sincrono_rechazado',
                [
                    'status_code' => $resultado['status_code'],
                    'glosa'       => $resultado['glosa'],
                ]
            );
        }

        return $resultado;
    }

    private function crearDocumentoTributario(
        int $concessionId,
        array $items,
        ReceptorData $receptor,
        ConfiguracionFiscal $config,
        Certificado $certificado,
        string $idempotencyKey,
        ?int $ordenServicioId
    ): DocumentoTributario {
        return DB::transaction(function () use ($concessionId, $items, $receptor, $config, $certificado, $idempotencyKey, $ordenServicioId) {
            $detalles = $this->construirDetalles($items);
            $impuestos = $this->calculator->calcularDetallesBoleta($detalles);

            $folioData = $this->folioAllocator->asignar($concessionId, 39);
            $folio = $folioData['folio'];
            $caf = $folioData['caf'];

            $this->cafManager->cargar($caf);

            $emisor = EmisorData::fromConfiguracion($config);

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
                'orden_servicio_id' => $ordenServicioId,
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
                    'orden_servicio_id' => $ordenServicioId,
                    'tipo' => $ordenServicioId ? 'orden_servicio' : 'venta_rapida',
                ],
                null,
                'emitido'
            );

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

    private function construirReceptorDesdeArray(?array $datos): ReceptorData
    {
        if (!$datos || empty($datos['rut'])) {
            return ReceptorData::anonimo();
        }

        return new ReceptorData([
            'rut' => $datos['rut'],
            'razon_social' => $datos['razon_social'] ?? 'Sin Nombre',
            'direccion' => $datos['direccion'] ?? null,
            'comuna' => $datos['comuna'] ?? null,
            'ciudad' => $datos['ciudad'] ?? null,
        ]);
    }
}
