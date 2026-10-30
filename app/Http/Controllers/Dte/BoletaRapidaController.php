<?php

namespace App\Http\Controllers\Dte;

use App\Http\Controllers\Controller;
use App\Models\Dte\DocumentoTributario;
use App\Services\Dte\DteService;
use App\Services\Dte\Pdf\DtePdfGenerator;
use Illuminate\Http\Request;
use Laracasts\Flash\Flash;

class BoletaRapidaController extends Controller
{
    public function create()
    {
        return view('dte.boleta_rapida.create');
    }

    public function store(Request $request, DteService $dteService, DtePdfGenerator $pdfGenerator)
    {
        $request->validate([
            'items' => 'required|array|min:1',
            'items.*.nombre' => 'required|string|max:80',
            'items.*.cantidad' => 'required|numeric|min:0.01',
            'items.*.precio_unitario' => 'required|integer|min:1',
            'receptor_rut' => 'nullable|string|max:12',
            'receptor_nombre' => 'nullable|string|max:100',
        ]);

        $concessionId = auth()->user()->id_concession;

        $datosReceptor = null;
        if ($request->filled('receptor_rut')) {
            $datosReceptor = [
                'rut' => $request->receptor_rut,
                'razon_social' => $request->receptor_nombre ?? 'Sin Nombre',
            ];
        }

        try {
            $documento = $dteService->emitirBoletaRapida(
                $concessionId,
                $request->items,
                $datosReceptor
            );

            if ($documento->track_id) {
                Flash::success("Boleta #{$documento->folio} emitida y enviada al SII (Track ID: {$documento->track_id}).");
                return redirect()->route('dte.boleta_rapida.resultado', $documento);
            }

            Flash::warning("Boleta #{$documento->folio} emitida pero el SII no confirmo recepcion. Revise el estado.");
            return redirect()->route('dte.boleta_rapida.resultado', $documento);
        } catch (\App\Exceptions\FoliosAgotadosException $e) {
            Flash::error('Sin folios disponibles. Suba un nuevo archivo CAF.');
            return back()->withInput();
        } catch (\App\Exceptions\DteException $e) {
            Flash::error("Error al emitir boleta: {$e->getMessage()}");
            return back()->withInput();
        }
    }

    public function resultado(DocumentoTributario $boleta, DtePdfGenerator $pdfGenerator)
    {
        if ($boleta->id_concession !== auth()->user()->id_concession) {
            abort(403);
        }

        $boleta->load('detalles');

        return view('dte.boleta_rapida.resultado', compact('boleta'));
    }

    public function pdf(DocumentoTributario $boleta, DtePdfGenerator $pdfGenerator)
    {
        if ($boleta->id_concession !== auth()->user()->id_concession) {
            abort(403);
        }

        $pdf = $pdfGenerator->generarBoleta($boleta);

        return $pdf->stream("boleta-{$boleta->folio}.pdf");
    }
}
