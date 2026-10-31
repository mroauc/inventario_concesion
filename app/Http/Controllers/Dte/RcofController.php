<?php

namespace App\Http\Controllers\Dte;

use App\Http\Controllers\Controller;
use App\Models\Dte\Certificado;
use App\Models\Dte\ConfiguracionFiscal;
use App\Models\Dte\DocumentoTributario;
use App\Models\Dte\EnvioSii;
use App\Services\Dte\Sii\RcofSender;
use Illuminate\Http\Request;
use Laracasts\Flash\Flash;

class RcofController extends Controller
{
    public function index()
    {
        $concessionId = auth()->user()->id_concession;

        $envios = EnvioSii::where('id_concession', $concessionId)
            ->where('tipo_envio', 'rcof')
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        $hoy = now()->format('Y-m-d');
        $ayer = now()->subDay()->format('Y-m-d');

        $boletasHoy = DocumentoTributario::where('id_concession', $concessionId)
            ->whereDate('fecha_emision', $hoy)
            ->whereIn('estado', ['emitido', 'enviado', 'aceptado'])
            ->count();

        $boletasAyer = DocumentoTributario::where('id_concession', $concessionId)
            ->whereDate('fecha_emision', $ayer)
            ->whereIn('estado', ['emitido', 'enviado', 'aceptado'])
            ->count();

        return view('dte.rcof.index', compact('envios', 'boletasHoy', 'boletasAyer', 'hoy', 'ayer'));
    }

    public function enviar(Request $request, RcofSender $rcofSender)
    {
        $request->validate([
            'fecha' => 'required|date|before_or_equal:today',
        ]);

        $concessionId = auth()->user()->id_concession;
        $fecha = $request->fecha;

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
            $resultado = $rcofSender->enviar($config, $certificado, $fecha);

            if ($resultado['exitoso']) {
                Flash::success("RCOF para {$fecha} enviado exitosamente. Track ID: {$resultado['track_id']}");
            } else {
                Flash::warning("RCOF enviado pero SII respondio: {$resultado['glosa']}");
            }
        } catch (\App\Exceptions\DteException $e) {
            Flash::error("Error al enviar RCOF: {$e->getMessage()}");
        }

        return redirect()->route('dte.rcof.index');
    }
}
