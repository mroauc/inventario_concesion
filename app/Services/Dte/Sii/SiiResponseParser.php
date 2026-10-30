<?php

namespace App\Services\Dte\Sii;

use App\Exceptions\DteException;
use DOMDocument;

class SiiResponseParser
{
    public function parsearRespuestaEnvio(string $xml): array
    {
        $doc = $this->cargarXml($xml);

        $trackId = $this->extraerValor($doc, 'TRACKID');
        $status = $this->extraerValor($doc, 'STATUS');
        $glosa = $this->extraerValor($doc, 'GLOSA', '');

        return [
            'track_id'    => $trackId ? (int) $trackId : null,
            'status_code' => $status !== null ? (int) $status : null,
            'glosa'       => $glosa,
            'exitoso'     => $status === '0',
        ];
    }

    public function parsearEstadoEnvio(string $xml): array
    {
        $doc = $this->cargarXml($xml);

        $estado = $this->extraerValor($doc, 'ESTADO');
        $glosa = $this->extraerValor($doc, 'GLOSA', '');
        $numAtencion = $this->extraerValor($doc, 'NUM_ATENCION');

        $estadoNormalizado = $this->normalizarEstadoEnvio($estado);

        return [
            'estado_raw'     => $estado,
            'estado'         => $estadoNormalizado,
            'glosa'          => $glosa,
            'num_atencion'   => $numAtencion,
            'es_final'       => in_array($estadoNormalizado, ['aceptado', 'rechazado']),
        ];
    }

    public function parsearEstadoDocumento(string $xml): array
    {
        $doc = $this->cargarXml($xml);

        $estado = $this->extraerValor($doc, 'ESTADO');
        $glosa = $this->extraerValor($doc, 'GLOSA_ERR', '');
        if (empty($glosa)) {
            $glosa = $this->extraerValor($doc, 'GLOSA', '');
        }

        return [
            'estado_raw' => $estado,
            'glosa'      => $glosa,
        ];
    }

    private function normalizarEstadoEnvio(?string $estadoSii): string
    {
        if ($estadoSii === null) {
            return 'desconocido';
        }

        $estadoSii = strtoupper(trim($estadoSii));

        $mapa = [
            'EPR' => 'aceptado',
            'SOK' => 'enviado',
            'CRT' => 'enviado',
            'FOK' => 'enviado',
            '-11' => 'enviado',
            'RSC' => 'rechazado',
            'RCT' => 'rechazado',
            'RFR' => 'rechazado',
            'RPT' => 'rechazado',
            'RPR' => 'rechazado',
            'RCH' => 'rechazado',
        ];

        return $mapa[$estadoSii] ?? 'enviado';
    }

    private function cargarXml(string $xml): DOMDocument
    {
        $doc = new DOMDocument();
        $xml = trim($xml);

        if (empty($xml)) {
            throw new DteException('Respuesta del SII vacia');
        }

        libxml_use_internal_errors(true);
        $loaded = $doc->loadXML($xml);
        libxml_clear_errors();

        if (!$loaded) {
            throw new DteException('Respuesta del SII no es XML valido');
        }

        return $doc;
    }

    private function extraerValor(DOMDocument $doc, string $tagName, ?string $default = null): ?string
    {
        $nodes = $doc->getElementsByTagName($tagName);
        if ($nodes->length === 0) {
            return $default;
        }

        return trim($nodes->item(0)->nodeValue);
    }
}
