<?php

namespace App\Services\Dte\Sii;

use App\Exceptions\DteException;
use App\Models\Dte\Certificado;
use App\Models\Dte\ConfiguracionFiscal;
use App\Services\Dte\Xml\XmlSigner;
use DOMDocument;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use SoapClient;

class SiiAuthClient
{
    private const ENDPOINTS = [
        'certificacion' => 'https://maullin.sii.cl/DTEWS/',
        'produccion'    => 'https://palena.sii.cl/DTEWS/',
    ];

    private const SEED_WSDL = 'CrSeed.jws?WSDL';
    private const TOKEN_WSDL = 'GetTokenFromSeed.jws?WSDL';

    private const TOKEN_TTL_MINUTES = 60;

    private XmlSigner $xmlSigner;

    public function __construct(XmlSigner $xmlSigner)
    {
        $this->xmlSigner = $xmlSigner;
    }

    public function obtenerToken(ConfiguracionFiscal $config, Certificado $certificado): string
    {
        $cacheKey = "sii_token:{$config->id_concession}:{$config->ambiente}";

        return Cache::remember($cacheKey, now()->addMinutes(self::TOKEN_TTL_MINUTES), function () use ($config, $certificado) {
            $semilla = $this->getSemilla($config->ambiente);

            $xmlFirmado = $this->firmarSemilla($semilla, $certificado);

            return $this->getToken($config->ambiente, $xmlFirmado);
        });
    }

    public function invalidarToken(int $concessionId, string $ambiente): void
    {
        Cache::forget("sii_token:{$concessionId}:{$ambiente}");
    }

    public function getBaseUrl(string $ambiente): string
    {
        if (!isset(self::ENDPOINTS[$ambiente])) {
            throw new DteException("Ambiente SII no valido: {$ambiente}");
        }

        return self::ENDPOINTS[$ambiente];
    }

    private function getSemilla(string $ambiente): string
    {
        $wsdl = $this->getBaseUrl($ambiente) . self::SEED_WSDL;

        try {
            $client = new SoapClient($wsdl, [
                'trace'              => true,
                'exceptions'         => true,
                'connection_timeout' => 10,
                'stream_context'     => stream_context_create([
                    'ssl' => ['verify_peer' => false, 'verify_peer_name' => false],
                ]),
            ]);

            $response = $client->getSeed();
        } catch (\SoapFault $e) {
            Log::error('SII getSemilla SOAP error', ['error' => $e->getMessage(), 'ambiente' => $ambiente]);
            throw new DteException("Error al obtener semilla del SII: {$e->getMessage()}");
        }

        $xml = new DOMDocument();
        if (!$xml->loadXML($response->getSeedReturn)) {
            throw new DteException('Respuesta de semilla SII no es XML valido');
        }

        $estado = $xml->getElementsByTagName('ESTADO')->item(0);
        if (!$estado || $estado->nodeValue !== '00') {
            $glosa = $xml->getElementsByTagName('GLOSA')->item(0);
            $msg = $glosa ? $glosa->nodeValue : 'Estado desconocido';
            throw new DteException("Error al obtener semilla: {$msg}");
        }

        $semilla = $xml->getElementsByTagName('SEMILLA')->item(0);
        if (!$semilla) {
            throw new DteException('No se encontro SEMILLA en la respuesta del SII');
        }

        return $semilla->nodeValue;
    }

    private function firmarSemilla(string $semilla, Certificado $certificado): string
    {
        $certData = $this->xmlSigner->cargarCertificado(
            $certificado->archivo_completo,
            $certificado->password_descifrada
        );

        $doc = new DOMDocument('1.0', 'UTF-8');
        $doc->formatOutput = false;

        $getToken = $doc->createElement('getToken');
        $doc->appendChild($getToken);

        $item = $doc->createElement('item');
        $getToken->appendChild($item);

        $semillaNode = $doc->createElement('Semilla', $semilla);
        $item->appendChild($semillaNode);

        $this->firmarGetToken($doc, $certData['private_key'], $certData['cert_pem']);

        return $doc->saveXML();
    }

    private function firmarGetToken(DOMDocument $doc, \OpenSSLAsymmetricKey $privateKey, string $certPem): void
    {
        $getToken = $doc->documentElement;

        $c14n = $getToken->C14N();
        $digestValue = base64_encode(sha1($c14n, true));

        $dsigNs = 'http://www.w3.org/2000/09/xmldsig#';

        $signature = $doc->createElementNS($dsigNs, 'Signature');

        $signedInfo = $doc->createElementNS($dsigNs, 'SignedInfo');

        $canonMethod = $doc->createElementNS($dsigNs, 'CanonicalizationMethod');
        $canonMethod->setAttribute('Algorithm', 'http://www.w3.org/TR/2001/REC-xml-c14n-20010315');
        $signedInfo->appendChild($canonMethod);

        $sigMethod = $doc->createElementNS($dsigNs, 'SignatureMethod');
        $sigMethod->setAttribute('Algorithm', 'http://www.w3.org/2000/09/xmldsig#rsa-sha1');
        $signedInfo->appendChild($sigMethod);

        $reference = $doc->createElementNS($dsigNs, 'Reference');
        $reference->setAttribute('URI', '');

        $transforms = $doc->createElementNS($dsigNs, 'Transforms');
        $transform = $doc->createElementNS($dsigNs, 'Transform');
        $transform->setAttribute('Algorithm', 'http://www.w3.org/2000/09/xmldsig#enveloped-signature');
        $transforms->appendChild($transform);
        $reference->appendChild($transforms);

        $digestMethod = $doc->createElementNS($dsigNs, 'DigestMethod');
        $digestMethod->setAttribute('Algorithm', 'http://www.w3.org/2000/09/xmldsig#sha1');
        $reference->appendChild($digestMethod);

        $reference->appendChild($doc->createElementNS($dsigNs, 'DigestValue', $digestValue));
        $signedInfo->appendChild($reference);

        $signature->appendChild($signedInfo);

        $c14nSignedInfo = $signedInfo->C14N();
        $firma = '';
        if (!openssl_sign($c14nSignedInfo, $firma, $privateKey, OPENSSL_ALGO_SHA1)) {
            throw new DteException('Error al firmar semilla: ' . openssl_error_string());
        }

        $signature->appendChild(
            $doc->createElementNS($dsigNs, 'SignatureValue', base64_encode($firma))
        );

        $keyInfo = $doc->createElementNS($dsigNs, 'KeyInfo');
        $x509Data = $doc->createElementNS($dsigNs, 'X509Data');
        $certClean = str_replace(
            ['-----BEGIN CERTIFICATE-----', '-----END CERTIFICATE-----', "\r", "\n"],
            '',
            $certPem
        );
        $x509Data->appendChild(
            $doc->createElementNS($dsigNs, 'X509Certificate', trim($certClean))
        );
        $keyInfo->appendChild($x509Data);
        $signature->appendChild($keyInfo);

        $getToken->appendChild($signature);
    }

    private function getToken(string $ambiente, string $xmlFirmado): string
    {
        $wsdl = $this->getBaseUrl($ambiente) . self::TOKEN_WSDL;

        try {
            $client = new SoapClient($wsdl, [
                'trace'              => true,
                'exceptions'         => true,
                'connection_timeout' => 10,
                'stream_context'     => stream_context_create([
                    'ssl' => ['verify_peer' => false, 'verify_peer_name' => false],
                ]),
            ]);

            $response = $client->getToken($xmlFirmado);
        } catch (\SoapFault $e) {
            Log::error('SII getToken SOAP error', ['error' => $e->getMessage(), 'ambiente' => $ambiente]);
            throw new DteException("Error al obtener token del SII: {$e->getMessage()}");
        }

        $xml = new DOMDocument();
        if (!$xml->loadXML($response->getTokenReturn)) {
            throw new DteException('Respuesta de token SII no es XML valido');
        }

        $estado = $xml->getElementsByTagName('ESTADO')->item(0);
        if (!$estado || $estado->nodeValue !== '00') {
            $glosa = $xml->getElementsByTagName('GLOSA')->item(0);
            $msg = $glosa ? $glosa->nodeValue : 'Estado desconocido';
            throw new DteException("Error al obtener token SII: {$msg}");
        }

        $token = $xml->getElementsByTagName('TOKEN')->item(0);
        if (!$token) {
            throw new DteException('No se encontro TOKEN en la respuesta del SII');
        }

        return $token->nodeValue;
    }
}
