<?php

namespace App\Http\Controllers;

use App\Models\Artefacto;
use App\Models\ArtefactoImport;
use App\Models\TipoArtefacto;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\ImportadorImport;

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
                  ->orWhere('codigo',      'like', "%{$search}%")
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
        $columnMap = [1 => 'codigo', 2 => 'marca', 3 => 'modelo', 4 => 'descripcion', 5 => 'estado'];
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
                e($artefacto->codigo ?? '—'),
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
            'codigo'            => 'nullable|string|max:255',
            'marca'             => 'nullable|string|max:255',
            'modelo'            => 'nullable|string|max:255',
            'descripcion'       => 'nullable|string',
            'tipo_artefacto_id' => 'nullable|exists:tipo_artefactos,id',
        ]);

        Artefacto::create([
            'codigo'            => $request->codigo,
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
            'codigo'            => 'nullable|string|max:255',
            'marca'             => 'nullable|string|max:255',
            'modelo'            => 'nullable|string|max:255',
            'descripcion'       => 'nullable|string',
            'tipo_artefacto_id' => 'nullable|exists:tipo_artefactos,id',
        ]);

        $artefacto->update([
            'codigo'            => $request->codigo,
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

    public function index_importar()
    {
        $tipos = TipoArtefacto::where('id_concession', auth()->user()->id_concession)
            ->orderBy('nombre')
            ->get();
        return view('artefactos.importar', compact('tipos'));
    }

    public function importar(Request $request)
    {
        $request->validate([
            'archivo' => 'required|file|mimes:xlsx,xls,csv,txt',
        ]);

        $inputFile  = $request->file('archivo');
        $extension  = strtolower($inputFile->getClientOriginalExtension());
        $nombreArchivo = $inputFile->getClientOriginalName();
        $idConcession  = auth()->user()->id_concession;

        $filas = Excel::toArray(new ImportadorImport(), $inputFile)[0];

        // Omitir encabezado (primera fila)
        $filas = array_slice($filas, 1);

        $tipos = TipoArtefacto::where('id_concession', $idConcession)
            ->get()
            ->keyBy(fn($t) => mb_strtolower(trim($t->nombre)));

        $validos  = [];
        $errores  = [];
        $numFila  = 2; // comienza en 2 por el encabezado

        foreach ($filas as $fila) {
            $codigo      = trim($fila[0] ?? '');
            $descripcion = trim($fila[1] ?? '');
            $modelo      = trim($fila[2] ?? '');
            $tipoNombre  = trim($fila[3] ?? '');
            $marca       = trim($fila[4] ?? '');

            if ($codigo === '' || $descripcion === '' || $modelo === '' || $tipoNombre === '' || $marca === '') {
                $errores[] = ['fila' => $numFila, 'motivo' => 'Todos los campos son obligatorios.'];
                $numFila++;
                continue;
            }

            $tipoKey = mb_strtolower($tipoNombre);
            if (!isset($tipos[$tipoKey])) {
                $errores[] = ['fila' => $numFila, 'motivo' => "El tipo de artefacto \"{$tipoNombre}\" no existe."];
                $numFila++;
                continue;
            }

            $tipoId = $tipos[$tipoKey]->id;

            $duplicado = Artefacto::where('id_concession', $idConcession)
                ->where('codigo', $codigo)
                ->where('marca', $marca)
                ->where('modelo', $modelo)
                ->where('tipo_artefacto_id', $tipoId)
                ->exists();

            if ($duplicado) {
                $errores[] = ['fila' => $numFila, 'motivo' => "Ya existe un artefacto con código \"{$codigo}\", marca \"{$marca}\" y modelo \"{$modelo}\"."];
                $numFila++;
                continue;
            }

            $validos[] = [
                'codigo'            => $codigo,
                'descripcion'       => $descripcion,
                'modelo'            => $modelo,
                'marca'             => $marca,
                'tipo_artefacto_id' => $tipoId,
                'id_concession'     => $idConcession,
                'estado'            => true,
            ];
            $numFila++;
        }

        $totalRows    = count($filas);
        $successCount = count($validos);
        $errorCount   = count($errores);

        try {
            \DB::beginTransaction();

            foreach ($validos as $datos) {
                Artefacto::create($datos);
            }

            ArtefactoImport::create([
                'id_user'       => auth()->user()->id,
                'id_concession' => $idConcession,
                'archivo'       => $nombreArchivo,
                'total_rows'    => $totalRows,
                'success_count' => $successCount,
                'error_count'   => $errorCount,
                'errors'        => $errores ?: null,
            ]);

            \App\Models\Log::create([
                'content'       => "Importación de Artefactos: {$successCount} creados, {$errorCount} rechazados. Archivo: {$nombreArchivo}",
                'activity'      => 'Importación',
                'id_user'       => auth()->user()->id,
                'id_concession' => $idConcession,
            ]);

            \DB::commit();
        } catch (\Exception $e) {
            \DB::rollback();
            return back()->with('error', 'Error durante la importación: ' . $e->getMessage());
        }

        $mensaje = "{$successCount} artefacto(s) importado(s) correctamente.";
        if ($errorCount > 0) {
            $mensaje .= " {$errorCount} fila(s) rechazada(s). Revisa el historial para ver los detalles.";
        }

        return redirect()->route('artefactos.historial')
            ->with('success', $mensaje);
    }

    public function historial()
    {
        $imports = ArtefactoImport::with('user')
            ->where('id_concession', auth()->user()->id_concession)
            ->orderByDesc('created_at')
            ->paginate(15);

        return view('artefactos.historial', compact('imports'));
    }
}
