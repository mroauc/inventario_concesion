<?php

namespace App\Services\Dte\Sii;

use App\Exceptions\DteException;
use Illuminate\Support\Facades\Log;
use SoapClient;

class SiiStatusClient
{
    private const QUERY_EST_UP_WSDL = 'QueryEstUp.jws?WSDL';

    private SiiAuthClient $authClient;
    private SiiResponseParser $responseParser;

    public function __construct(SiiAuthClient $authClient, SiiResponseParser $responseParser)
    {
        $this->authClient = $authClient;
        $this->responseParser = $responseParser;
    }

    public function consultarEstadoEnvio(
        string $ambiente,
        string $token,
        string $rutEmpresa,
        int $trackId
    ): array {
        $wsdl = $this->authClient->getBaseUrl($ambiente) . self::QUERY_EST_UP_WSDL;

        [$rut, $dv] = $this->separarRut($rutEmpresa);

        try {
            $client = new SoapClient($wsdl, [
                'trace'              => true,
                'exceptions'         => true,
                'connection_timeout' => 10,
                'stream_context'     => stream_context_create([
                    'ssl' => ['verify_peer' => false, 'verify_peer_name' => false],
                ]),
            ]);

            $response = $client->getEstUp(
                $rut,
                $dv,
                (string) $trackId,
                $token
            );
        } catch (\SoapFault $e) {
            Log::error('SII consultarEstadoEnvio SOAP error', [
                'error'    => $e->getMessage(),
                'track_id' => $trackId,
                'ambiente' => $ambiente,
            ]);
            throw new DteException("Error al consultar estado del envio: {$e->getMessage()}");
        }

        $xmlResponse = is_string($response) ? $response : ($response->getEstUpReturn ?? '');

        return $this->responseParser->parsearEstadoEnvio($xmlResponse);
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
