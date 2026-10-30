<?php

namespace App\Http\Controllers\Dte;

use App\Http\Controllers\Controller;
use App\Models\Dte\Certificado;
use App\Models\Dte\ConfiguracionFiscal;
use App\Models\Dte\DocumentoTributario;
use App\Services\Dte\Sii\SiiAuthClient;
use App\Services\Dte\Sii\SiiStatusClient;
use App\Services\Dte\Sync\DteStatusSynchronizer;
use Illuminate\Http\Request;
use Laracasts\Flash\Flash;

class DteConsultaController extends Controller
{
    public function index()
    {
        $concessionId = auth()->user()->id_concession;

        $documentos = DocumentoTributario::where('id_concession', $concessionId)
            ->whereIn('estado', ['enviado', 'aceptado', 'rechazado'])
            ->orderBy('updated_at', 'desc')
            ->paginate(20);

        return view('dte.consulta.index', compact('documentos'));
    }

    public function consultar(
        DocumentoTributario $documento,
        SiiAuthClient $authClient,
        SiiStatusClient $statusClient
    ) {
        $concessionId = auth()->user()->id_concession;

        if ($documento->id_concession !== $concessionId) {
            abort(403);
        }

        if (!$documento->track_id) {
            Flash::warning('Este documento no tiene Track ID. No se ha enviado al SII.');
            return back();
        }

        $config = ConfiguracionFiscal::where('id_concession', $concessionId)
            ->activa()
            ->first();

        $certificado = Certificado::where('id_concession', $concessionId)
            ->activo()
            ->vigente()
            ->first();

        if (!$config || !$certificado) {
            Flash::error('Configuracion fiscal o certificado no disponible.');
            return back();
        }

        try {
            $token = $authClient->obtenerToken($config, $certificado);

            $resultado = $statusClient->consultarEstadoEnvio(
                $config->ambiente,
                $token,
                $config->rut_empresa,
                $documento->track_id
            );

            if ($resultado['es_final'] && $resultado['estado'] !== $documento->estado) {
                $documento->update([
                    'estado' => $resultado['estado'],
                    'respuesta_sii' => $resultado['glosa'],
                ]);
                Flash::success("Estado actualizado a: {$resultado['estado']}");
            } else {
                Flash::info("Estado actual en SII: {$resultado['estado_raw']} — {$resultado['glosa']}");
            }
        } catch (\App\Exceptions\DteException $e) {
            Flash::error("Error al consultar SII: {$e->getMessage()}");
        }

        return back();
    }

    public function sincronizarTodos(DteStatusSynchronizer $synchronizer)
    {
        $concessionId = auth()->user()->id_concession;

        $resultados = $synchronizer->sincronizar($concessionId);

        if ($resultados['total'] === 0) {
            Flash::info('No hay documentos pendientes de sincronizacion.');
        } else {
            Flash::success(
                "Sincronizacion completada: {$resultados['actualizados']} actualizados de {$resultados['total']} pendientes."
                . ($resultados['errores'] > 0 ? " ({$resultados['errores']} errores)" : '')
            );
        }

        return redirect()->route('dte.consulta.index');
    }
}
