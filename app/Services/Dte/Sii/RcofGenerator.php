<?php

namespace App\Services\Dte\Sii;

use App\Models\Dte\Certificado;
use App\Models\Dte\ConfiguracionFiscal;
use App\Models\Dte\DocumentoTributario;
use App\Services\Dte\Xml\XmlSigner;
use DOMDocument;

class RcofGenerator
{
    private XmlSigner $xmlSigner;

    private const SII_NS = 'http://www.sii.cl/SiiDte';

    public function __construct(XmlSigner $xmlSigner)
    {
        $this->xmlSigner = $xmlSigner;
    }

    public function generar(
        ConfiguracionFiscal $config,
        Certificado $certificado,
        string $fecha,
        int $secuenciaEnvio = 1
    ): string {
        $documentos = DocumentoTributario::where('id_concession', $config->id_concession)
            ->whereDate('fecha_emision', $fecha)
            ->whereIn('estado', ['emitido', 'enviado', 'aceptado'])
            ->get();

        $resumenPorTipo = $this->calcularResumen($documentos);

        $doc = new DOMDocument('1.0', 'ISO-8859-1');
        $doc->formatOutput = false;

        $root = $doc->createElementNS(self::SII_NS, 'ConsumoFolios');
        $root->setAttribute('version', '1.0');
        $doc->appendChild($root);

        $certData = $this->xmlSigner->cargarCertificado(
            $certificado->archivo_completo,
            $certificado->password_descifrada
        );

        $docConsumo = $doc->createElement('DocumentoConsumoFolios');
        $consumoId = "ROAVAL_RCOF_{$config->rut_empresa}_{$fecha}_{$secuenciaEnvio}";
        $docConsumo->setAttribute('ID', $consumoId);
        $root->appendChild($docConsumo);

        $this->agregarCaratula($doc, $docConsumo, $config, $certData['rut_titular'], $fecha, $secuenciaEnvio);

        foreach ($resumenPorTipo as $tipo => $datos) {
            $this->agregarResumen($doc, $docConsumo, $tipo, $datos);
        }

        if (empty($resumenPorTipo)) {
            $this->agregarResumenVacio($doc, $docConsumo, 39);
        }

        $this->xmlSigner->firmar(
            $doc,
            $consumoId,
            $certData['private_key'],
            $certData['cert_pem']
        );

        return $doc->saveXML();
    }

    private function calcularResumen($documentos): array
    {
        $resumen = [];

        foreach ($documentos as $doc) {
            $tipo = $doc->tipo_documento;

            if (!isset($resumen[$tipo])) {
                $resumen[$tipo] = [
                    'mnt_neto' => 0,
                    'mnt_iva' => 0,
                    'tasa_iva' => 19,
                    'mnt_exento' => 0,
                    'mnt_total' => 0,
                    'folios_emitidos' => 0,
                    'folios_anulados' => 0,
                    'folios_utilizados' => 0,
                    'folio_min' => PHP_INT_MAX,
                    'folio_max' => 0,
                ];
            }

            $r = &$resumen[$tipo];

            if ($doc->estado === 'anulado') {
                $r['folios_anulados']++;
            } else {
                $r['mnt_neto'] += $doc->monto_neto;
                $r['mnt_iva'] += $doc->monto_iva;
                $r['mnt_exento'] += $doc->monto_exento;
                $r['mnt_total'] += $doc->monto_total;
                $r['folios_emitidos']++;
            }

            $r['folios_utilizados']++;
            $r['folio_min'] = min($r['folio_min'], $doc->folio);
            $r['folio_max'] = max($r['folio_max'], $doc->folio);
        }

        return $resumen;
    }

    private function agregarCaratula(
        DOMDocument $doc,
        \DOMElement $parent,
        ConfiguracionFiscal $config,
        string $rutEnvia,
        string $fecha,
        int $secuencia
    ): void {
        $caratula = $doc->createElement('Caratula');
        $caratula->setAttribute('version', '1.0');

        $caratula->appendChild($doc->createElement('RutEmisor', $config->rut_empresa));
        $caratula->appendChild($doc->createElement('RutEnvia', $rutEnvia));
        $caratula->appendChild($doc->createElement('FchResol', $config->fecha_resolucion->format('Y-m-d')));
        $caratula->appendChild($doc->createElement('NroResol', (string) $config->numero_resolucion));
        $caratula->appendChild($doc->createElement('FchInicio', $fecha));
        $caratula->appendChild($doc->createElement('FchFinal', $fecha));
        $caratula->appendChild($doc->createElement('SecEnvio', (string) $secuencia));
        $caratula->appendChild($doc->createElement('TmstFirmaEnv', date('Y-m-d\TH:i:s')));

        $parent->appendChild($caratula);
    }

    private function agregarResumen(DOMDocument $doc, \DOMElement $parent, int $tipo, array $datos): void
    {
        $resumen = $doc->createElement('Resumen');

        $resumen->appendChild($doc->createElement('TipoDocumento', (string) $tipo));
        $resumen->appendChild($doc->createElement('MntNeto', (string) $datos['mnt_neto']));
        $resumen->appendChild($doc->createElement('MntIva', (string) $datos['mnt_iva']));
        $resumen->appendChild($doc->createElement('TasaIVA', (string) $datos['tasa_iva']));
        $resumen->appendChild($doc->createElement('MntExento', (string) $datos['mnt_exento']));
        $resumen->appendChild($doc->createElement('MntTotal', (string) $datos['mnt_total']));
        $resumen->appendChild($doc->createElement('FoliosEmitidos', (string) $datos['folios_emitidos']));
        $resumen->appendChild($doc->createElement('FoliosAnulados', (string) $datos['folios_anulados']));
        $resumen->appendChild($doc->createElement('FoliosUtilizados', (string) $datos['folios_utilizados']));

        if ($datos['folios_utilizados'] > 0) {
            $rango = $doc->createElement('RangoUtilizados');
            $rango->appendChild($doc->createElement('Inicial', (string) $datos['folio_min']));
            $rango->appendChild($doc->createElement('Final', (string) $datos['folio_max']));
            $resumen->appendChild($rango);
        }

        $parent->appendChild($resumen);
    }

    private function agregarResumenVacio(DOMDocument $doc, \DOMElement $parent, int $tipo): void
    {
        $resumen = $doc->createElement('Resumen');
        $resumen->appendChild($doc->createElement('TipoDocumento', (string) $tipo));
        $resumen->appendChild($doc->createElement('MntTotal', '0'));
        $resumen->appendChild($doc->createElement('FoliosEmitidos', '0'));
        $resumen->appendChild($doc->createElement('FoliosAnulados', '0'));
        $resumen->appendChild($doc->createElement('FoliosUtilizados', '0'));
        $parent->appendChild($resumen);
    }
}
