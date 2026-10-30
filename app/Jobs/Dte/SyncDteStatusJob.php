<?php

namespace App\Jobs\Dte;

use App\Exceptions\DteException;
use App\Models\Dte\AuditDte;
use App\Models\Dte\Certificado;
use App\Models\Dte\ConfiguracionFiscal;
use App\Models\Dte\DocumentoTributario;
use App\Services\Dte\Sii\SiiAuthClient;
use App\Services\Dte\Sii\SiiStatusClient;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SyncDteStatusJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 1;
    public int $timeout = 300;

    private ?int $concessionId;

    public function __construct(?int $concessionId = null)
    {
        $this->concessionId = $concessionId;
    }

    public function handle(SiiAuthClient $authClient, SiiStatusClient $statusClient): void
    {
        $query = DocumentoTributario::where('estado', 'enviado')
            ->whereNotNull('track_id');

        if ($this->concessionId) {
            $query->where('id_concession', $this->concessionId);
        }

        $documentos = $query->get();

        if ($documentos->isEmpty()) {
            Log::info('SyncDteStatusJob: no hay documentos pendientes de sincronizacion');
            return;
        }

        $tokenCache = [];

        foreach ($documentos as $documento) {
            try {
                $this->sincronizarDocumento($documento, $authClient, $statusClient, $tokenCache);
            } catch (\Throwable $e) {
                Log::error('Error al sincronizar estado de documento', [
                    'documento_id' => $documento->id,
                    'error'        => $e->getMessage(),
                ]);
            }
        }
    }

    private function sincronizarDocumento(
        DocumentoTributario $documento,
        SiiAuthClient $authClient,
        SiiStatusClient $statusClient,
        array &$tokenCache
    ): void {
        $concessionId = $documento->id_concession;

        if (!isset($tokenCache[$concessionId])) {
            $config = ConfiguracionFiscal::where('id_concession', $concessionId)
                ->activa()
                ->firstOrFail();

            $certificado = Certificado::where('id_concession', $concessionId)
                ->activo()
                ->vigente()
                ->firstOrFail();

            $tokenCache[$concessionId] = [
                'token'   => $authClient->obtenerToken($config, $certificado),
                'config'  => $config,
            ];
        }

        $cached = $tokenCache[$concessionId];
        $config = $cached['config'];
        $token = $cached['token'];

        $resultado = $statusClient->consultarEstadoEnvio(
            $config->ambiente,
            $token,
            $config->rut_empresa,
            $documento->track_id
        );

        Log::info('Estado SII consultado', [
            'documento_id' => $documento->id,
            'track_id'     => $documento->track_id,
            'estado_raw'   => $resultado['estado_raw'],
            'estado'       => $resultado['estado'],
        ]);

        if ($resultado['es_final']) {
            $estadoAnterior = $documento->estado;
            $documento->update([
                'estado'       => $resultado['estado'],
                'respuesta_sii' => $resultado['glosa'],
            ]);

            AuditDte::registrar(
                $documento->id_concession,
                $documento->id,
                'estado_sii_actualizado',
                [
                    'estado_raw'   => $resultado['estado_raw'],
                    'glosa'        => $resultado['glosa'],
                    'num_atencion' => $resultado['num_atencion'],
                ],
                $estadoAnterior,
                $resultado['estado']
            );
        }
    }

    public function failed(\Throwable $exception): void
    {
        Log::error('SyncDteStatusJob definitivamente fallido', [
            'concession_id' => $this->concessionId,
            'error'         => $exception->getMessage(),
        ]);
    }
}
