<?php

namespace App\Services\Dte\Xml;

use App\Exceptions\DteException;
use DOMDocument;
use DOMElement;

class XmlSigner
{
    private const DSIG_NS = 'http://www.w3.org/2000/09/xmldsig#';
    private const C14N_URI = 'http://www.w3.org/TR/2001/REC-xml-c14n-20010315';
    private const RSA_SHA1_URI = 'http://www.w3.org/2000/09/xmldsig#rsa-sha1';
    private const SHA1_URI = 'http://www.w3.org/2000/09/xmldsig#sha1';
    private const ENVELOPED_URI = 'http://www.w3.org/2000/09/xmldsig#enveloped-signature';

    public function firmar(
        DOMDocument $doc,
        string $referenceId,
        \OpenSSLAsymmetricKey $privateKey,
        string $x509CertPem
    ): void {
        $element = $this->buscarElementoPorId($doc, $referenceId);

        $digestValue = $this->computarDigest($element);

        $signedInfo = $this->construirSignedInfo($doc, $referenceId, $digestValue);

        $signatureValue = $this->computarSignature($signedInfo, $privateKey);

        $keyInfo = $this->construirKeyInfo($doc, $privateKey, $x509CertPem);

        $signature = $doc->createElementNS(self::DSIG_NS, 'Signature');
        $signature->appendChild($signedInfo);
        $signature->appendChild(
            $doc->createElementNS(self::DSIG_NS, 'SignatureValue', $signatureValue)
        );
        $signature->appendChild($keyInfo);

        $element->parentNode->appendChild($signature);
    }

    public function cargarCertificado(string $p12Path, string $password): array
    {
        $contenido = file_get_contents($p12Path);
        if ($contenido === false) {
            throw new DteException("No se pudo leer el certificado: {$p12Path}");
        }

        $certs = [];
        if (!openssl_pkcs12_read($contenido, $certs, $password)) {
            throw new DteException('No se pudo leer el certificado .p12. Verifique la contrasena.');
        }

        $pkey = openssl_pkey_get_private($certs['pkey']);
        if (!$pkey) {
            throw new DteException('No se pudo extraer la clave privada del certificado.');
        }

        $x509 = openssl_x509_parse($certs['cert']);
        $rutTitular = $x509['subject']['serialNumber'] ?? null;

        return [
            'private_key' => $pkey,
            'cert_pem' => $certs['cert'],
            'rut_titular' => $rutTitular,
        ];
    }

    private function buscarElementoPorId(DOMDocument $doc, string $id): DOMElement
    {
        $xpath = new \DOMXPath($doc);
        $xpath->registerNamespace('sii', 'http://www.sii.cl/SiiDte');

        $queries = [
            "//*[@ID='{$id}']",
            "//sii:Documento[@ID='{$id}']",
            "//sii:SetDTE[@ID='{$id}']",
        ];

        foreach ($queries as $query) {
            $nodes = $xpath->query($query);
            if ($nodes && $nodes->length > 0) {
                return $nodes->item(0);
            }
        }

        throw new DteException("No se encontro elemento con ID={$id}");
    }

    private function computarDigest(DOMElement $element): string
    {
        $c14n = $element->C14N();
        return base64_encode(sha1($c14n, true));
    }

    private function construirSignedInfo(DOMDocument $doc, string $referenceId, string $digestValue): DOMElement
    {
        $signedInfo = $doc->createElementNS(self::DSIG_NS, 'SignedInfo');

        $canonMethod = $doc->createElementNS(self::DSIG_NS, 'CanonicalizationMethod');
        $canonMethod->setAttribute('Algorithm', self::C14N_URI);
        $signedInfo->appendChild($canonMethod);

        $sigMethod = $doc->createElementNS(self::DSIG_NS, 'SignatureMethod');
        $sigMethod->setAttribute('Algorithm', self::RSA_SHA1_URI);
        $signedInfo->appendChild($sigMethod);

        $reference = $doc->createElementNS(self::DSIG_NS, 'Reference');
        $reference->setAttribute('URI', "#{$referenceId}");

        $transforms = $doc->createElementNS(self::DSIG_NS, 'Transforms');
        $transform = $doc->createElementNS(self::DSIG_NS, 'Transform');
        $transform->setAttribute('Algorithm', self::ENVELOPED_URI);
        $transforms->appendChild($transform);
        $reference->appendChild($transforms);

        $digestMethod = $doc->createElementNS(self::DSIG_NS, 'DigestMethod');
        $digestMethod->setAttribute('Algorithm', self::SHA1_URI);
        $reference->appendChild($digestMethod);

        $reference->appendChild(
            $doc->createElementNS(self::DSIG_NS, 'DigestValue', $digestValue)
        );

        $signedInfo->appendChild($reference);

        return $signedInfo;
    }

    private function computarSignature(DOMElement $signedInfo, \OpenSSLAsymmetricKey $privateKey): string
    {
        $c14n = $signedInfo->C14N();
        $firma = '';

        if (!openssl_sign($c14n, $firma, $privateKey, OPENSSL_ALGO_SHA1)) {
            throw new DteException('Error al firmar SignedInfo: ' . openssl_error_string());
        }

        return base64_encode($firma);
    }

    private function construirKeyInfo(
        DOMDocument $doc,
        \OpenSSLAsymmetricKey $privateKey,
        string $x509CertPem
    ): DOMElement {
        $keyInfo = $doc->createElementNS(self::DSIG_NS, 'KeyInfo');

        $keyDetails = openssl_pkey_get_details($privateKey);
        if ($keyDetails && isset($keyDetails['rsa'])) {
            $keyValue = $doc->createElementNS(self::DSIG_NS, 'KeyValue');
            $rsaKeyValue = $doc->createElementNS(self::DSIG_NS, 'RSAKeyValue');

            $modulus = base64_encode($keyDetails['rsa']['n']);
            $exponent = base64_encode($keyDetails['rsa']['e']);

            $rsaKeyValue->appendChild(
                $doc->createElementNS(self::DSIG_NS, 'Modulus', $modulus)
            );
            $rsaKeyValue->appendChild(
                $doc->createElementNS(self::DSIG_NS, 'Exponent', $exponent)
            );
            $keyValue->appendChild($rsaKeyValue);
            $keyInfo->appendChild($keyValue);
        }

        $x509Data = $doc->createElementNS(self::DSIG_NS, 'X509Data');
        $certClean = $this->limpiarCertificadoPem($x509CertPem);
        $x509Data->appendChild(
            $doc->createElementNS(self::DSIG_NS, 'X509Certificate', $certClean)
        );
        $keyInfo->appendChild($x509Data);

        return $keyInfo;
    }

    private function limpiarCertificadoPem(string $pem): string
    {
        $pem = str_replace('-----BEGIN CERTIFICATE-----', '', $pem);
        $pem = str_replace('-----END CERTIFICATE-----', '', $pem);
        return trim(str_replace(["\r", "\n"], '', $pem));
    }
}
