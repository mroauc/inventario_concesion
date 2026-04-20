<?php

namespace App\Http\Controllers;

use App\Models\TipoArtefacto;
use Illuminate\Http\Request;

class TipoArtefactoController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Request $request)
    {
        $tipos = TipoArtefacto::where('id_concession', auth()->user()->id_concession);

        if ($request->filled('search')) {
            $search = $request->get('search');
            $tipos->where(function ($q) use ($search) {
                $q->where('nombre', 'like', "%{$search}%")
                  ->orWhere('descripcion', 'like', "%{$search}%");
            });
        }

        $tipos = $tipos->paginate(15);
        return view('tipo_artefactos.index', compact('tipos'));
    }

    public function create()
    {
        return view('tipo_artefactos.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'nombre'      => 'required|string|max:255',
            'descripcion' => 'nullable|string',
        ]);

        TipoArtefacto::create([
            'nombre'        => $request->nombre,
            'descripcion'   => $request->descripcion,
            'id_concession' => auth()->user()->id_concession,
        ]);

        return redirect()->route('tipo_artefactos.index')
            ->with('success', 'Tipo de artefacto creado exitosamente.');
    }

    public function edit(TipoArtefacto $tipo_artefacto)
    {
        abort_if($tipo_artefacto->id_concession != auth()->user()->id_concession, 403);
        return view('tipo_artefactos.edit', compact('tipo_artefacto'));
    }

    public function update(Request $request, TipoArtefacto $tipo_artefacto)
    {
        abort_if($tipo_artefacto->id_concession != auth()->user()->id_concession, 403);

        $request->validate([
            'nombre'      => 'required|string|max:255',
            'descripcion' => 'nullable|string',
        ]);

        $tipo_artefacto->update($request->only('nombre', 'descripcion'));

        return redirect()->route('tipo_artefactos.index')
            ->with('success', 'Tipo de artefacto actualizado exitosamente.');
    }

    public function destroy(TipoArtefacto $tipo_artefacto)
    {
        abort_if($tipo_artefacto->id_concession != auth()->user()->id_concession, 403);

        if ($tipo_artefacto->artefactos()->count() > 0) {
            return redirect()->route('tipo_artefactos.index')
                ->with('error', 'No se puede eliminar: existen artefactos asociados a este tipo.');
        }

        $tipo_artefacto->delete();

        return redirect()->route('tipo_artefactos.index')
            ->with('success', 'Tipo de artefacto eliminado exitosamente.');
    }
}
