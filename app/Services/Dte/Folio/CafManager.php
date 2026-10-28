<?php

namespace App\Services\Dte\Folio;

use App\Exceptions\DteException;
use App\Models\Dte\CafFolio;
use DOMDocument;
use DOMXPath;

class CafManager
{
    private ?DOMDocument $doc = null;
    private ?DOMXPath $xpath = null;

    public function cargar(CafFolio $caf): self
    {
        $xmlPlano = $caf->xml_descifrado;

        $this->doc = new DOMDocument();
        if (!@$this->doc->loadXML($xmlPlano)) {
            throw new DteException('No se pudo parsear el XML del CAF.');
        }

        $this->xpath = new DOMXPath($this->doc);

        return $this;
    }

    public function getClavePrivada(): \OpenSSLAsymmetricKey
    {
        $this->verificarCargado();

        $rsask = $this->xpathValue('//AUTORIZACION/RSASK');
        if (!$rsask) {
            throw new DteException('El CAF no contiene clave privada (RSASK).');
        }

        $pkey = openssl_pkey_get_private($rsask);
        if (!$pkey) {
            throw new DteException('No se pudo leer la clave privada del CAF.');
        }

        return $pkey;
    }

    public function getCafXmlNodo(): string
    {
        $this->verificarCargado();

        $cafNodes = $this->xpath->query('//AUTORIZACION/CAF');
        if (!$cafNodes || $cafNodes->length === 0) {
            throw new DteException('No se encontro nodo CAF en el XML.');
        }

        return $this->doc->saveXML($cafNodes->item(0));
    }

    public function getRutEmisor(): string
    {
        $this->verificarCargado();
        return $this->xpathValue('//AUTORIZACION/CAF/DA/RE') ?? '';
    }

    public function getTipoDocumento(): int
    {
        $this->verificarCargado();
        return (int) ($this->xpathValue('//AUTORIZACION/CAF/DA/TD') ?? 0);
    }

    public function getRangoDesde(): int
    {
        $this->verificarCargado();
        return (int) ($this->xpathValue('//AUTORIZACION/CAF/DA/RNG/D') ?? 0);
    }

    public function getRangoHasta(): int
    {
        $this->verificarCargado();
        return (int) ($this->xpathValue('//AUTORIZACION/CAF/DA/RNG/H') ?? 0);
    }

    public function getIdk(): int
    {
        $this->verificarCargado();
        return (int) ($this->xpathValue('//AUTORIZACION/CAF/DA/IDK') ?? 0);
    }

    private function xpathValue(string $expression): ?string
    {
        $nodes = $this->xpath->query($expression);
        if ($nodes && $nodes->length > 0) {
            return trim($nodes->item(0)->textContent);
        }
        return null;
    }

    private function verificarCargado(): void
    {
        if (!$this->doc || !$this->xpath) {
            throw new DteException('CAF no cargado. Llame a cargar() primero.');
        }
    }
}
