<?php

namespace App\Http\Controllers;

use App\Models\Artefacto;
use App\Models\TipoArtefacto;
use Illuminate\Http\Request;

class ArtefactoController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        return view('artefactos.index');
    }

    public function datatables(Request $request)
    {
        $draw   = $request->input('draw', 1);
        $start  = $request->input('start', 0);
        $length = $request->input('length', 15);
        $search = $request->input('search.value', '');

        $query = Artefacto::with('tipoArtefacto')
            ->where('id_concession', auth()->user()->id_concession);

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('marca',        'like', "%{$search}%")
                  ->orWhere('modelo',      'like', "%{$search}%")
                  ->orWhere('descripcion', 'like', "%{$search}%")
                  ->orWhereHas('tipoArtefacto', function ($q2) use ($search) {
                      $q2->where('nombre', 'like', "%{$search}%");
                  });
            });
        }

        $total    = Artefacto::where('id_concession', auth()->user()->id_concession)->count();
        $filtered = $query->count();

        $orderCol  = $request->input('order.0.column', 0);
        $orderDir  = $request->input('order.0.dir', 'asc') === 'asc' ? 'asc' : 'desc';
        $columnMap = [1 => 'marca', 2 => 'modelo', 3 => 'descripcion', 4 => 'estado'];
        if (isset($columnMap[$orderCol])) {
            $query->orderBy($columnMap[$orderCol], $orderDir);
        } else {
            // col 0 = tipo: ordenar por tipo_artefacto_id como proxy
            $query->orderBy('tipo_artefacto_id', $orderDir)->orderBy('marca', 'asc');
        }

        $artefactos = $query->skip($start)->take($length)->get();

        $data = $artefactos->map(function ($artefacto) {
            $estado   = '<span class="badge badge-' . ($artefacto->estado ? 'success' : 'danger') . '">'
                      . ($artefacto->estado ? 'Activo' : 'Inactivo') . '</span>';

            $acciones = '
                <div class="btn-group">
                    <a href="' . route('artefactos.show', $artefacto->id) . '" class="btn btn-default btn-xs"><i class="far fa-eye"></i></a>
                    <a href="' . route('artefactos.edit', $artefacto->id) . '" class="btn btn-default btn-xs"><i class="far fa-edit"></i></a>
                    <form method="POST" action="' . route('artefactos.destroy', $artefacto->id) . '" style="display:inline">
                        <input type="hidden" name="_token" value="' . csrf_token() . '">
                        <input type="hidden" name="_method" value="DELETE">
                        <button type="submit" class="btn btn-danger btn-xs" onclick="return confirm(\'¿Está seguro de eliminar este artefacto?\')"><i class="far fa-trash-alt"></i></button>
                    </form>
                </div>';

            return [
                e($artefacto->tipoArtefacto->nombre ?? '—'),
                e($artefacto->marca ?? '—'),
                e($artefacto->modelo ?? '—'),
                e(\Str::limit($artefacto->descripcion ?? '', 50) ?: '—'),
                $estado,
                $acciones,
            ];
        });

        return response()->json([
            'draw'            => (int) $draw,
            'recordsTotal'    => $total,
            'recordsFiltered' => $filtered,
            'data'            => $data,
        ]);
    }

    public function create()
    {
        $tipos = TipoArtefacto::where('id_concession', auth()->user()->id_concession)
            ->orderBy('nombre')
            ->get();
        return view('artefactos.create', compact('tipos'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'marca'             => 'nullable|string|max:255',
            'modelo'            => 'nullable|string|max:255',
            'descripcion'       => 'nullable|string',
            'tipo_artefacto_id' => 'nullable|exists:tipo_artefactos,id',
        ]);

        Artefacto::create([
            'marca'             => $request->marca,
            'modelo'            => $request->modelo,
            'descripcion'       => $request->descripcion,
            'estado'            => $request->boolean('estado', true),
            'tipo_artefacto_id' => $request->tipo_artefacto_id,
            'id_concession'     => auth()->user()->id_concession,
        ]);

        return redirect()->route('artefactos.index')
            ->with('success', 'Artefacto creado exitosamente.');
    }

    public function show(Artefacto $artefacto)
    {
        abort_if($artefacto->id_concession != auth()->user()->id_concession, 403);
        $artefacto->load('tipoArtefacto');
        return view('artefactos.show', compact('artefacto'));
    }

    public function edit(Artefacto $artefacto)
    {
        abort_if($artefacto->id_concession != auth()->user()->id_concession, 403);
        $tipos = TipoArtefacto::where('id_concession', auth()->user()->id_concession)
            ->orderBy('nombre')
            ->get();
        return view('artefactos.edit', compact('artefacto', 'tipos'));
    }

    public function update(Request $request, Artefacto $artefacto)
    {
        abort_if($artefacto->id_concession != auth()->user()->id_concession, 403);

        $request->validate([
            'marca'             => 'nullable|string|max:255',
            'modelo'            => 'nullable|string|max:255',
            'descripcion'       => 'nullable|string',
            'tipo_artefacto_id' => 'nullable|exists:tipo_artefactos,id',
        ]);

        $artefacto->update([
            'marca'             => $request->marca,
            'modelo'            => $request->modelo,
            'descripcion'       => $request->descripcion,
            'estado'            => $request->boolean('estado', true),
            'tipo_artefacto_id' => $request->tipo_artefacto_id,
        ]);

        return redirect()->route('artefactos.index')
            ->with('success', 'Artefacto actualizado exitosamente.');
    }

    public function destroy(Artefacto $artefacto)
    {
        abort_if($artefacto->id_concession != auth()->user()->id_concession, 403);

        if ($artefacto->ordenesServicio()->count() > 0) {
            return redirect()->route('artefactos.index')
                ->with('error', 'No se puede eliminar: este artefacto está asociado a órdenes de servicio.');
        }

        $artefacto->delete();

        return redirect()->route('artefactos.index')
            ->with('success', 'Artefacto eliminado exitosamente.');
    }
}
