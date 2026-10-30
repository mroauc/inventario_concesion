<?php

namespace App\Services\Dte\Sii;

use App\Exceptions\DteException;
use App\Models\Dte\ConfiguracionFiscal;
use Illuminate\Support\Facades\Log;

class SiiBoletaClient
{
    private const UPLOAD_PATH = '/cgi_dte/UPL/DTEUpload';

    private const MAX_RETRIES = 3;
    private const BASE_DELAY_SECONDS = 2;
    private const TIMEOUT_SECONDS = 30;

    private SiiAuthClient $authClient;
    private SiiResponseParser $responseParser;

    public function __construct(SiiAuthClient $authClient, SiiResponseParser $responseParser)
    {
        $this->authClient = $authClient;
        $this->responseParser = $responseParser;
    }

    public function enviar(
        ConfiguracionFiscal $config,
        string $token,
        string $rutCertificado,
        string $xmlSobre
    ): array {
        $url = $this->construirUrl($config->ambiente);

        [$rutSender, $dvSender] = $this->separarRut($rutCertificado);
        [$rutCompany, $dvCompany] = $this->separarRut($config->rut_empresa);

        $xmlIso = mb_convert_encoding($xmlSobre, 'ISO-8859-1', 'UTF-8');

        $tmpFile = tempnam(sys_get_temp_dir(), 'dte_');
        file_put_contents($tmpFile, $xmlIso);

        try {
            $response = $this->enviarConRetry(
                $url,
                $token,
                $rutSender,
                $dvSender,
                $rutCompany,
                $dvCompany,
                $tmpFile
            );
        } finally {
            @unlink($tmpFile);
        }

        return $this->responseParser->parsearRespuestaEnvio($response);
    }

    private function enviarConRetry(
        string $url,
        string $token,
        string $rutSender,
        string $dvSender,
        string $rutCompany,
        string $dvCompany,
        string $filePath
    ): string {
        $lastException = null;

        for ($intento = 1; $intento <= self::MAX_RETRIES; $intento++) {
            try {
                return $this->ejecutarEnvio(
                    $url,
                    $token,
                    $rutSender,
                    $dvSender,
                    $rutCompany,
                    $dvCompany,
                    $filePath
                );
            } catch (DteException $e) {
                $lastException = $e;

                Log::warning('SII envio boleta intento fallido', [
                    'intento' => $intento,
                    'max'     => self::MAX_RETRIES,
                    'error'   => $e->getMessage(),
                ]);

                if ($intento < self::MAX_RETRIES) {
                    $delay = self::BASE_DELAY_SECONDS * pow(2, $intento - 1);
                    sleep($delay);
                }
            }
        }

        throw new DteException(
            "Error al enviar boleta al SII tras " . self::MAX_RETRIES . " intentos: " .
            ($lastException ? $lastException->getMessage() : 'Error desconocido')
        );
    }

    private function ejecutarEnvio(
        string $url,
        string $token,
        string $rutSender,
        string $dvSender,
        string $rutCompany,
        string $dvCompany,
        string $filePath
    ): string {
        $postFields = [
            'rutSender'  => $rutSender,
            'dvSender'   => $dvSender,
            'rutCompany' => $rutCompany,
            'dvCompany'  => $dvCompany,
            'archivo'    => new \CURLFile($filePath, 'text/xml', 'envio.xml'),
        ];

        $ch = curl_init();

        curl_setopt_array($ch, [
            CURLOPT_URL            => $url,
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => $postFields,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => self::TIMEOUT_SECONDS,
            CURLOPT_CONNECTTIMEOUT => 10,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => 0,
            CURLOPT_HTTPHEADER     => [
                'User-Agent: Mozilla/5.0 (compatible; ROAVAL-DTE/1.0)',
                "Cookie: TOKEN={$token}",
            ],
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        if ($response === false) {
            throw new DteException("Error de conexion con SII: {$error}");
        }

        if ($httpCode < 200 || $httpCode >= 300) {
            throw new DteException("SII respondio con HTTP {$httpCode}");
        }

        return $response;
    }

    private function construirUrl(string $ambiente): string
    {
        $host = $ambiente === 'produccion' ? 'palena.sii.cl' : 'maullin.sii.cl';
        return "https://{$host}" . self::UPLOAD_PATH;
    }

    private function separarRut(string $rut): array
    {
        $rut = str_replace('.', '', $rut);
        $partes = explode('-', $rut);

        if (count($partes) !== 2) {
            throw new DteException("Formato de RUT invalido: {$rut}");
        }

        return [$partes[0], $partes[1]];
    }
}
