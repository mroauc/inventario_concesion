<?php

namespace App\Services\Dte\Sync;

use App\Models\Dte\AuditDte;
use App\Models\Dte\Certificado;
use App\Models\Dte\ConfiguracionFiscal;
use App\Models\Dte\DocumentoTributario;
use App\Services\Dte\Sii\SiiAuthClient;
use App\Services\Dte\Sii\SiiStatusClient;
use Illuminate\Support\Facades\Log;

class DteStatusSynchronizer
{
    private SiiAuthClient $authClient;
    private SiiStatusClient $statusClient;

    public function __construct(SiiAuthClient $authClient, SiiStatusClient $statusClient)
    {
        $this->authClient = $authClient;
        $this->statusClient = $statusClient;
    }

    public function sincronizar(?int $concessionId = null): array
    {
        $query = DocumentoTributario::where('estado', 'enviado')
            ->whereNotNull('track_id');

        if ($concessionId) {
            $query->where('id_concession', $concessionId);
        }

        $documentos = $query->get();
        $resultados = ['total' => $documentos->count(), 'actualizados' => 0, 'errores' => 0];

        if ($documentos->isEmpty()) {
            return $resultados;
        }

        $tokenCache = [];

        foreach ($documentos as $documento) {
            try {
                $actualizado = $this->sincronizarDocumento($documento, $tokenCache);
                if ($actualizado) {
                    $resultados['actualizados']++;
                }
            } catch (\Throwable $e) {
                $resultados['errores']++;
                Log::error('DteStatusSynchronizer: error al sincronizar', [
                    'documento_id' => $documento->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        return $resultados;
    }

    private function sincronizarDocumento(DocumentoTributario $documento, array &$tokenCache): bool
    {
        $concessionId = $documento->id_concession;

        if (!isset($tokenCache[$concessionId])) {
            $config = ConfiguracionFiscal::where('id_concession', $concessionId)
                ->activa()
                ->first();

            if (!$config) {
                Log::warning('DteStatusSynchronizer: sin configuracion fiscal', [
                    'concession_id' => $concessionId,
                ]);
                return false;
            }

            $certificado = Certificado::where('id_concession', $concessionId)
                ->activo()
                ->vigente()
                ->first();

            if (!$certificado) {
                Log::warning('DteStatusSynchronizer: sin certificado activo', [
                    'concession_id' => $concessionId,
                ]);
                return false;
            }

            $tokenCache[$concessionId] = [
                'token' => $this->authClient->obtenerToken($config, $certificado),
                'config' => $config,
            ];
        }

        $cached = $tokenCache[$concessionId];

        $resultado = $this->statusClient->consultarEstadoEnvio(
            $cached['config']->ambiente,
            $cached['token'],
            $cached['config']->rut_empresa,
            $documento->track_id
        );

        if ($resultado['es_final']) {
            $estadoAnterior = $documento->estado;
            $documento->update([
                'estado' => $resultado['estado'],
                'respuesta_sii' => $resultado['glosa'],
            ]);

            AuditDte::registrar(
                $documento->id_concession,
                $documento->id,
                'estado_sii_sincronizado',
                [
                    'estado_raw' => $resultado['estado_raw'],
                    'glosa' => $resultado['glosa'],
                    'num_atencion' => $resultado['num_atencion'],
                ],
                $estadoAnterior,
                $resultado['estado']
            );

            return true;
        }

        return false;
    }
}
