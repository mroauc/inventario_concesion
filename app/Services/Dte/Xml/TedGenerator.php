<?php

namespace App\Services\Dte\Xml;

use App\DTOs\BoletaData;
use App\Exceptions\DteException;
use App\Services\Dte\Folio\CafManager;
use DOMDocument;

class TedGenerator
{
    public function generar(BoletaData $boleta, CafManager $cafManager): string
    {
        $doc = new DOMDocument('1.0', 'ISO-8859-1');
        $doc->formatOutput = false;

        $ted = $doc->createElement('TED');
        $ted->setAttribute('version', '1.0');

        $dd = $doc->createElement('DD');

        $dd->appendChild($doc->createElement('RE', $boleta->emisor->rutEmisor));
        $dd->appendChild($doc->createElement('TD', (string) $boleta->tipoDocumento));
        $dd->appendChild($doc->createElement('F', (string) $boleta->folio));
        $dd->appendChild($doc->createElement('FE', $boleta->fechaEmision));
        $dd->appendChild($doc->createElement('RR', $boleta->receptor->rutRecep));
        $dd->appendChild($doc->createElement('RSR', mb_substr($boleta->receptor->razonSocialRecep, 0, 40)));
        $dd->appendChild($doc->createElement('MNT', (string) $boleta->montoTotal));
        $dd->appendChild($doc->createElement('IT1', mb_substr($boleta->detalles[0]->nombreItem, 0, 40)));

        $cafXml = $cafManager->getCafXmlNodo();
        $cafFragment = $doc->createDocumentFragment();
        $cafFragment->appendXML($cafXml);
        $dd->appendChild($cafFragment);

        $dd->appendChild($doc->createElement('TSTED', date('Y-m-d\TH:i:s')));

        $ted->appendChild($dd);
        $doc->appendChild($ted);

        $ddC14n = $dd->C14N();
        $ddIso = mb_convert_encoding($ddC14n, 'ISO-8859-1', 'UTF-8');
        $ddFlat = preg_replace('/>\s+</', '><', $ddIso);

        $rsask = $cafManager->getClavePrivada();

        $firma = '';
        if (!openssl_sign($ddFlat, $firma, $rsask, OPENSSL_ALGO_SHA1)) {
            throw new DteException('Error al firmar el TED: ' . openssl_error_string());
        }

        $frmt = $doc->createElement('FRMT', base64_encode($firma));
        $frmt->setAttribute('algoritmo', 'SHA1withRSA');
        $ted->appendChild($frmt);

        return $doc->saveXML($ted);
    }

    public function insertarEnDocumento(DOMDocument $dteDoc, string $tedXml, string $documentoId): void
    {
        $fragment = $dteDoc->createDocumentFragment();
        $fragment->appendXML($tedXml);

        $xpath = new \DOMXPath($dteDoc);
        $xpath->registerNamespace('sii', 'http://www.sii.cl/SiiDte');

        $documentos = $xpath->query("//sii:Documento[@ID='{$documentoId}']");
        if (!$documentos || $documentos->length === 0) {
            throw new DteException("No se encontro Documento con ID={$documentoId}");
        }

        $documento = $documentos->item(0);

        $tmstFirma = $xpath->query("sii:TmstFirma", $documento);
        if ($tmstFirma && $tmstFirma->length > 0) {
            $documento->insertBefore($fragment, $tmstFirma->item(0));
        } else {
            $documento->appendChild($fragment);
        }
    }
}
