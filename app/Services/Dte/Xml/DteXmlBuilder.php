<?php

namespace App\Services\Dte\Xml;

use App\DTOs\BoletaData;
use App\DTOs\CaratulaData;
use DOMDocument;

class DteXmlBuilder
{
    private const SII_NS = 'http://www.sii.cl/SiiDte';

    public function construirBoleta39(BoletaData $boleta): DOMDocument
    {
        $doc = new DOMDocument('1.0', 'ISO-8859-1');
        $doc->formatOutput = false;

        $dte = $doc->createElementNS(self::SII_NS, 'DTE');
        $dte->setAttribute('version', '1.0');
        $doc->appendChild($dte);

        $documento = $doc->createElement('Documento');
        $documento->setAttribute('ID', $boleta->getDocumentoId());
        $dte->appendChild($documento);

        $this->agregarEncabezado($doc, $documento, $boleta);
        $this->agregarDetalles($doc, $documento, $boleta);

        $documento->appendChild(
            $doc->createElement('TmstFirma', date('Y-m-d\TH:i:s'))
        );

        return $doc;
    }

    public function construirEnvioBoleta(DOMDocument $dteDoc, CaratulaData $caratula): DOMDocument
    {
        $envio = new DOMDocument('1.0', 'ISO-8859-1');
        $envio->formatOutput = false;

        $root = $envio->createElementNS(self::SII_NS, 'EnvioBOLETA');
        $root->setAttribute('version', '1.0');
        $envio->appendChild($root);

        $setDte = $envio->createElement('SetDTE');
        $setDte->setAttribute('ID', 'DTE_SetDoc');
        $root->appendChild($setDte);

        $this->agregarCaratula($envio, $setDte, $caratula);

        $dteImport = $envio->importNode($dteDoc->documentElement, true);
        $setDte->appendChild($dteImport);

        return $envio;
    }

    private function agregarEncabezado(DOMDocument $doc, \DOMElement $documento, BoletaData $boleta): void
    {
        $encabezado = $doc->createElement('Encabezado');
        $documento->appendChild($encabezado);

        $idDoc = $doc->createElement('IdDoc');
        $idDoc->appendChild($doc->createElement('TipoDTE', (string) $boleta->tipoDocumento));
        $idDoc->appendChild($doc->createElement('Folio', (string) $boleta->folio));
        $idDoc->appendChild($doc->createElement('FchEmis', $boleta->fechaEmision));
        if ($boleta->indServicio !== null) {
            $idDoc->appendChild($doc->createElement('IndServicio', (string) $boleta->indServicio));
        }
        $encabezado->appendChild($idDoc);

        $emisor = $doc->createElement('Emisor');
        $emisor->appendChild($doc->createElement('RUTEmisor', $boleta->emisor->rutEmisor));
        $emisor->appendChild($doc->createElement('RznSocEmisor', $this->iso($boleta->emisor->razonSocial)));
        $emisor->appendChild($doc->createElement('GiroEmisor', $this->iso($boleta->emisor->giro)));
        if ($boleta->emisor->actividadEconomica) {
            $emisor->appendChild($doc->createElement('ActEco', (string) $boleta->emisor->actividadEconomica));
        }
        $emisor->appendChild($doc->createElement('DirOrigen', $this->iso($boleta->emisor->direccion)));
        $emisor->appendChild($doc->createElement('CmnaOrigen', $this->iso($boleta->emisor->comuna)));
        $emisor->appendChild($doc->createElement('CiudadOrigen', $this->iso($boleta->emisor->ciudad)));
        if ($boleta->emisor->sucursalSii > 0) {
            $emisor->appendChild($doc->createElement('Sucursal', (string) $boleta->emisor->sucursalSii));
        }
        $encabezado->appendChild($emisor);

        $receptor = $doc->createElement('Receptor');
        $receptor->appendChild($doc->createElement('RUTRecep', $boleta->receptor->rutRecep));
        $receptor->appendChild($doc->createElement('RznSocRecep', $this->iso($boleta->receptor->razonSocialRecep)));
        if ($boleta->receptor->direccion) {
            $receptor->appendChild($doc->createElement('DirRecep', $this->iso($boleta->receptor->direccion)));
        }
        if ($boleta->receptor->comuna) {
            $receptor->appendChild($doc->createElement('CmnaRecep', $this->iso($boleta->receptor->comuna)));
        }
        if ($boleta->receptor->ciudad) {
            $receptor->appendChild($doc->createElement('CiudadRecep', $this->iso($boleta->receptor->ciudad)));
        }
        $encabezado->appendChild($receptor);

        $totales = $doc->createElement('Totales');
        if ($boleta->montoNeto > 0) {
            $totales->appendChild($doc->createElement('MntNeto', (string) $boleta->montoNeto));
        }
        if ($boleta->montoIva > 0) {
            $totales->appendChild($doc->createElement('IVA', (string) $boleta->montoIva));
        }
        if ($boleta->montoExento > 0) {
            $totales->appendChild($doc->createElement('MntExe', (string) $boleta->montoExento));
        }
        $totales->appendChild($doc->createElement('MntTotal', (string) $boleta->montoTotal));
        $encabezado->appendChild($totales);
    }

    private function agregarDetalles(DOMDocument $doc, \DOMElement $documento, BoletaData $boleta): void
    {
        foreach ($boleta->detalles as $item) {
            $detalle = $doc->createElement('Detalle');
            $detalle->appendChild($doc->createElement('NroLinDet', (string) $item->nroLinea));
            $detalle->appendChild($doc->createElement('NmbItem', $this->iso($item->nombreItem)));

            if ($item->descripcion) {
                $detalle->appendChild($doc->createElement('DscItem', $this->iso(mb_substr($item->descripcion, 0, 1000))));
            }
            if ($item->unidadMedida) {
                $detalle->appendChild($doc->createElement('UnmdItem', $item->unidadMedida));
            }

            $detalle->appendChild($doc->createElement('QtyItem', $this->formatCantidad($item->cantidad)));
            $detalle->appendChild($doc->createElement('PrcItem', (string) $item->precioUnitario));

            if ($item->descuentoMonto > 0) {
                $detalle->appendChild($doc->createElement('DescuentoMonto', (string) $item->descuentoMonto));
            }

            $detalle->appendChild($doc->createElement('MontoItem', (string) $item->montoItem));
            $documento->appendChild($detalle);
        }
    }

    private function agregarCaratula(DOMDocument $doc, \DOMElement $setDte, CaratulaData $caratula): void
    {
        $cara = $doc->createElement('Caratula');
        $cara->setAttribute('version', '1.0');

        $cara->appendChild($doc->createElement('RutEmisor', $caratula->rutEmisor));
        $cara->appendChild($doc->createElement('RutEnvia', $caratula->rutEnvia));
        $cara->appendChild($doc->createElement('RutReceptor', $caratula->rutReceptor));
        $cara->appendChild($doc->createElement('FchResol', $caratula->fechaResolucion));
        $cara->appendChild($doc->createElement('NroResol', (string) $caratula->numeroResolucion));
        $cara->appendChild($doc->createElement('TmstFirmaEnv', $caratula->timestampFirma));

        foreach ($caratula->subTotales as $tipo => $cantidad) {
            $sub = $doc->createElement('SubTotDTE');
            $sub->appendChild($doc->createElement('TpoDTE', (string) $tipo));
            $sub->appendChild($doc->createElement('NroDTE', (string) $cantidad));
            $cara->appendChild($sub);
        }

        $firstChild = $setDte->firstChild;
        if ($firstChild) {
            $setDte->insertBefore($cara, $firstChild);
        } else {
            $setDte->appendChild($cara);
        }
    }

    private function iso(string $text): string
    {
        return mb_convert_encoding($text, 'ISO-8859-1', 'UTF-8');
    }

    private function formatCantidad(float $cantidad): string
    {
        if ($cantidad == (int) $cantidad) {
            return (string) (int) $cantidad;
        }
        return rtrim(rtrim(number_format($cantidad, 6, '.', ''), '0'), '.');
    }
}
