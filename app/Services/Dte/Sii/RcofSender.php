<?php

namespace App\Services\Dte\Sii;

use App\Exceptions\DteException;
use App\Models\Dte\AuditDte;
use App\Models\Dte\Certificado;
use App\Models\Dte\ConfiguracionFiscal;
use App\Models\Dte\EnvioSii;
use App\Services\Dte\Xml\XmlSigner;
use Illuminate\Support\Facades\Log;

class RcofSender
{
    private RcofGenerator $rcofGenerator;
    private SiiAuthClient $authClient;
    private SiiResponseParser $responseParser;
    private XmlSigner $xmlSigner;

    private const UPLOAD_PATH = '/cgi_dte/UPL/DTEUpload';
    private const TIMEOUT_SECONDS = 30;

    public function __construct(
        RcofGenerator $rcofGenerator,
        SiiAuthClient $authClient,
        SiiResponseParser $responseParser,
        XmlSigner $xmlSigner
    ) {
        $this->rcofGenerator = $rcofGenerator;
        $this->authClient = $authClient;
        $this->responseParser = $responseParser;
        $this->xmlSigner = $xmlSigner;
    }

    public function enviar(ConfiguracionFiscal $config, Certificado $certificado, string $fecha): array
    {
        $ultimoEnvio = EnvioSii::where('id_concession', $config->id_concession)
            ->where('tipo_envio', 'rcof')
            ->whereDate('created_at', $fecha)
            ->orderBy('id', 'desc')
            ->first();

        $secuencia = $ultimoEnvio ? ((int) $ultimoEnvio->intentos + 1) : 1;

        $xmlRcof = $this->rcofGenerator->generar($config, $certificado, $fecha, $secuencia);

        $token = $this->authClient->obtenerToken($config, $certificado);

        $certData = $this->xmlSigner->cargarCertificado(
            $certificado->archivo_completo,
            $certificado->password_descifrada
        );

        $respuestaRaw = $this->ejecutarEnvio(
            $config->ambiente,
            $token,
            $certData['rut_titular'],
            $config->rut_empresa,
            $xmlRcof
        );

        $resultado = $this->responseParser->parsearRespuestaEnvio($respuestaRaw);

        EnvioSii::create([
            'id_concession'    => $config->id_concession,
            'tipo_envio'       => 'rcof',
            'documento_id'     => null,
            'xml_enviado_hash' => hash('sha256', $xmlRcof),
            'respuesta_raw'    => json_encode($resultado),
            'track_id'         => $resultado['track_id'],
            'status_code'      => $resultado['status_code'],
            'http_status'      => 200,
            'intentos'         => $secuencia,
            'ambiente'         => $config->ambiente,
        ]);

        $accion = $resultado['exitoso'] ? 'rcof_enviado_exitoso' : 'rcof_enviado_rechazado';

        AuditDte::registrar(
            $config->id_concession,
            null,
            $accion,
            [
                'fecha'       => $fecha,
                'secuencia'   => $secuencia,
                'track_id'    => $resultado['track_id'],
                'status_code' => $resultado['status_code'],
                'glosa'       => $resultado['glosa'],
            ]
        );

        Log::info("RCOF {$accion}", [
            'concession' => $config->id_concession,
            'fecha'      => $fecha,
            'track_id'   => $resultado['track_id'],
        ]);

        return array_merge($resultado, [
            'fecha'     => $fecha,
            'secuencia' => $secuencia,
            'xml'       => $xmlRcof,
        ]);
    }

    public function enviarTodasLasConcesiones(string $fecha): array
    {
        $configs = ConfiguracionFiscal::activa()->get();
        $resultados = [];

        foreach ($configs as $config) {
            $certificado = Certificado::where('id_concession', $config->id_concession)
                ->activo()
                ->vigente()
                ->first();

            if (!$certificado) {
                Log::warning('RCOF: sin certificado vigente', ['concession' => $config->id_concession]);
                $resultados[] = [
                    'concession' => $config->id_concession,
                    'exitoso'    => false,
                    'glosa'      => 'Sin certificado vigente',
                ];
                continue;
            }

            try {
                $resultado = $this->enviar($config, $certificado, $fecha);
                $resultados[] = [
                    'concession' => $config->id_concession,
                    'exitoso'    => $resultado['exitoso'],
                    'track_id'   => $resultado['track_id'],
                    'glosa'      => $resultado['glosa'],
                ];
            } catch (DteException $e) {
                Log::error('RCOF error', [
                    'concession' => $config->id_concession,
                    'error'      => $e->getMessage(),
                ]);
                $resultados[] = [
                    'concession' => $config->id_concession,
                    'exitoso'    => false,
                    'glosa'      => $e->getMessage(),
                ];
            }
        }

        return $resultados;
    }

    private function ejecutarEnvio(
        string $ambiente,
        string $token,
        string $rutCertificado,
        string $rutEmpresa,
        string $xmlRcof
    ): string {
        $host = $ambiente === 'produccion' ? 'palena.sii.cl' : 'maullin.sii.cl';
        $url = "https://{$host}" . self::UPLOAD_PATH;

        $rutCert = str_replace('.', '', $rutCertificado);
        $rutEmp = str_replace('.', '', $rutEmpresa);
        [$rutSender, $dvSender] = explode('-', $rutCert);
        [$rutCompany, $dvCompany] = explode('-', $rutEmp);

        $xmlIso = mb_convert_encoding($xmlRcof, 'ISO-8859-1', 'UTF-8');

        $tmpFile = tempnam(sys_get_temp_dir(), 'rcof_');
        file_put_contents($tmpFile, $xmlIso);

        try {
            $ch = curl_init();
            curl_setopt_array($ch, [
                CURLOPT_URL            => $url,
                CURLOPT_POST           => true,
                CURLOPT_POSTFIELDS     => [
                    'rutSender'  => $rutSender,
                    'dvSender'   => $dvSender,
                    'rutCompany' => $rutCompany,
                    'dvCompany'  => $dvCompany,
                    'archivo'    => new \CURLFile($tmpFile, 'text/xml', 'rcof.xml'),
                ],
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
        } finally {
            @unlink($tmpFile);
        }

        if ($response === false) {
            throw new DteException("Error de conexion con SII al enviar RCOF: {$error}");
        }

        if ($httpCode < 200 || $httpCode >= 300) {
            throw new DteException("SII respondio con HTTP {$httpCode} al recibir RCOF");
        }

        return $response;
    }
}
