<?php

namespace App\Jobs\Dte;

use App\Exceptions\DteException;
use App\Models\Dte\AuditDte;
use App\Models\Dte\Certificado;
use App\Models\Dte\ConfiguracionFiscal;
use App\Models\Dte\DocumentoTributario;
use App\Models\Dte\EnvioSii;
use App\Services\Dte\Sii\SiiAuthClient;
use App\Services\Dte\Sii\SiiBoletaClient;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendBoletaToSiiJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $timeout = 120;

    private int $documentoId;

    public function __construct(int $documentoId)
    {
        $this->documentoId = $documentoId;
    }

    public function handle(SiiAuthClient $authClient, SiiBoletaClient $boletaClient): void
    {
        $documento = DocumentoTributario::findOrFail($this->documentoId);

        if (!in_array($documento->estado, ['emitido', 'enviado'])) {
            Log::info('SendBoletaToSiiJob: documento no esta en estado emitido/enviado', [
                'documento_id' => $this->documentoId,
                'estado'       => $documento->estado,
            ]);
            return;
        }

        $config = ConfiguracionFiscal::where('id_concession', $documento->id_concession)
            ->activa()
            ->firstOrFail();

        $certificado = Certificado::where('id_concession', $documento->id_concession)
            ->activo()
            ->vigente()
            ->firstOrFail();

        try {
            $token = $authClient->obtenerToken($config, $certificado);

            $certData = app(\App\Services\Dte\Xml\XmlSigner::class)->cargarCertificado(
                $certificado->archivo_completo,
                $certificado->password_descifrada
            );
            $rutCertificado = $certData['rut_titular'];

            $resultado = $boletaClient->enviar(
                $config,
                $token,
                $rutCertificado,
                $documento->xml_sobre
            );

            $envio = EnvioSii::create([
                'id_concession'   => $documento->id_concession,
                'tipo_envio'      => 'boleta',
                'documento_id'    => $documento->id,
                'xml_enviado_hash' => hash('sha256', $documento->xml_sobre),
                'respuesta_raw'   => json_encode($resultado),
                'track_id'        => $resultado['track_id'],
                'status_code'     => $resultado['status_code'],
                'http_status'     => 200,
                'intentos'        => $this->attempts(),
                'ambiente'        => $config->ambiente,
            ]);

            if ($resultado['exitoso'] && $resultado['track_id']) {
                $estadoAnterior = $documento->estado;
                $documento->update([
                    'estado'         => 'enviado',
                    'track_id'       => $resultado['track_id'],
                    'envio_intentos' => $this->attempts(),
                    'ultimo_envio_at' => now(),
                ]);

                AuditDte::registrar(
                    $documento->id_concession,
                    $documento->id,
                    'envio_sii_exitoso',
                    [
                        'track_id'    => $resultado['track_id'],
                        'status_code' => $resultado['status_code'],
                        'intentos'    => $this->attempts(),
                    ],
                    $estadoAnterior,
                    'enviado'
                );

                Log::info('Boleta enviada al SII exitosamente', [
                    'documento_id' => $documento->id,
                    'track_id'     => $resultado['track_id'],
                ]);
            } else {
                $documento->update([
                    'envio_intentos'  => $this->attempts(),
                    'ultimo_envio_at' => now(),
                    'respuesta_sii'   => $resultado['glosa'],
                ]);

                AuditDte::registrar(
                    $documento->id_concession,
                    $documento->id,
                    'envio_sii_rechazado',
                    [
                        'status_code' => $resultado['status_code'],
                        'glosa'       => $resultado['glosa'],
                    ]
                );

                Log::warning('Boleta rechazada por SII', [
                    'documento_id' => $documento->id,
                    'status'       => $resultado['status_code'],
                    'glosa'        => $resultado['glosa'],
                ]);
            }
        } catch (DteException $e) {
            $documento->update([
                'envio_intentos'  => $this->attempts(),
                'ultimo_envio_at' => now(),
                'respuesta_sii'   => $e->getMessage(),
            ]);

            AuditDte::registrar(
                $documento->id_concession,
                $documento->id,
                'envio_sii_error',
                ['error' => $e->getMessage()]
            );

            Log::error('Error al enviar boleta al SII', [
                'documento_id' => $this->documentoId,
                'error'        => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    public function failed(\Throwable $exception): void
    {
        Log::error('SendBoletaToSiiJob definitivamente fallido', [
            'documento_id' => $this->documentoId,
            'error'        => $exception->getMessage(),
        ]);
    }
}
