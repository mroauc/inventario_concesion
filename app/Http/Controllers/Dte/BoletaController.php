<?php

namespace App\Http\Controllers\Dte;

use App\Http\Controllers\Controller;
use App\Models\Dte\DocumentoTributario;
use App\Models\OrdenServicio;
use App\Services\Dte\DteService;
use App\Services\Dte\Pdf\DtePdfGenerator;
use Illuminate\Http\Request;
use Laracasts\Flash\Flash;

class BoletaController extends Controller
{
    public function index()
    {
        $concessionId = auth()->user()->id_concession;

        $boletas = DocumentoTributario::where('id_concession', $concessionId)
            ->where('tipo_documento', 39)
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('dte.boletas.index', compact('boletas'));
    }

    public function create(Request $request)
    {
        $concessionId = auth()->user()->id_concession;

        $ordenId = $request->query('orden_servicio_id');
        $orden = null;

        if ($ordenId) {
            $orden = OrdenServicio::where('id_concession', $concessionId)
                ->with(['cliente', 'detalles.producto', 'detalles.servicio'])
                ->findOrFail($ordenId);
        }

        return view('dte.boletas.create', compact('orden'));
    }

    public function store(Request $request, DteService $dteService)
    {
        $request->validate([
            'orden_servicio_id' => 'required|integer|exists:ordenes_servicio,id',
            'items' => 'required|array|min:1',
            'items.*.nombre' => 'required|string|max:80',
            'items.*.cantidad' => 'required|numeric|min:0.01',
            'items.*.precio_unitario' => 'required|integer|min:1',
        ]);

        $concessionId = auth()->user()->id_concession;

        $orden = OrdenServicio::where('id_concession', $concessionId)
            ->findOrFail($request->orden_servicio_id);

        try {
            $documento = $dteService->emitirBoleta($concessionId, $orden, $request->items);

            Flash::success("Boleta #{$documento->folio} emitida correctamente. Enviando al SII...");
            return redirect()->route('dte.boletas.show', $documento);
        } catch (\App\Exceptions\FoliosAgotadosException $e) {
            Flash::error('Sin folios disponibles. Suba un nuevo CAF.');
            return back()->withInput();
        } catch (\App\Exceptions\DteException $e) {
            Flash::error("Error al emitir boleta: {$e->getMessage()}");
            return back()->withInput();
        }
    }

    public function show(DocumentoTributario $boleta)
    {
        if ($boleta->id_concession !== auth()->user()->id_concession) {
            abort(403);
        }

        $boleta->load(['detalles', 'envios', 'ordenServicio.cliente']);

        return view('dte.boletas.show', compact('boleta'));
    }

    public function pdf(DocumentoTributario $boleta, DtePdfGenerator $pdfGenerator)
    {
        if ($boleta->id_concession !== auth()->user()->id_concession) {
            abort(403);
        }

        $pdf = $pdfGenerator->generarBoleta($boleta);

        return $pdf->download("boleta-{$boleta->folio}.pdf");
    }
}
