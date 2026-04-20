<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateClienteRequest;
use App\Http\Requests\UpdateClienteRequest;
use App\Models\Cliente;
use Illuminate\Http\Request;

class ClienteController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        return view('clientes.index');
    }

    public function datatables(Request $request)
    {
        $draw   = $request->input('draw', 1);
        $start  = $request->input('start', 0);
        $length = $request->input('length', 15);
        $search = $request->input('search.value', '');

        $query = Cliente::where('id_concession', auth()->user()->id_concession);

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('nombre', 'like', "%{$search}%")
                  ->orWhere('apellido', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('rut', 'like', "%{$search}%")
                  ->orWhere('numero_contacto', 'like', "%{$search}%");
            });
        }

        $total    = Cliente::where('id_concession', auth()->user()->id_concession)->count();
        $filtered = $query->count();

        $orderCol  = $request->input('order.0.column', 1);
        $orderDir  = $request->input('order.0.dir', 'asc') === 'asc' ? 'asc' : 'desc';
        $columnMap = [0 => 'rut', 1 => 'nombre', 2 => 'email', 3 => 'numero_contacto', 4 => 'tipo_cliente', 5 => 'estado'];
        $sortColumn = $columnMap[$orderCol] ?? 'nombre';
        $query->orderBy($sortColumn, $orderDir);

        $clientes = $query->skip($start)->take($length)->get();

        $data = $clientes->map(function ($cliente) {
            $tipoBadgeClass = $cliente->tipo_cliente === 'empresa' ? 'primary' : ($cliente->tipo_cliente === 'concesion' ? 'success' : 'secondary');
            $tipoCliente    = '<span class="badge badge-' . $tipoBadgeClass . '">' . ucfirst(e($cliente->tipo_cliente)) . '</span>';
            $estadoBadge    = '<span class="badge badge-' . ($cliente->estado ? 'success' : 'danger') . '">' . ($cliente->estado ? 'Activo' : 'Inactivo') . '</span>';

            $acciones = '
                <div class="btn-group">
                    <a href="' . route('clientes.show', $cliente->id) . '" class="btn btn-default btn-xs"><i class="far fa-eye"></i></a>
                    <a href="' . route('clientes.edit', $cliente->id) . '" class="btn btn-default btn-xs"><i class="far fa-edit"></i></a>
                    <form method="POST" action="' . route('clientes.destroy', $cliente->id) . '" style="display:inline">
                        <input type="hidden" name="_token" value="' . csrf_token() . '">
                        <input type="hidden" name="_method" value="DELETE">
                        <button type="submit" class="btn btn-danger btn-xs" onclick="return confirm(\'¿Está seguro?\')"><i class="far fa-trash-alt"></i></button>
                    </form>
                </div>';

            return [
                e($cliente->rut ?? '-'),
                e($cliente->nombre) . ' ' . e($cliente->apellido),
                e($cliente->email ?? '-'),
                e($cliente->numero_contacto ?? '-'),
                $tipoCliente,
                $estadoBadge,
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
        return view('clientes.create');
    }

    public function store(Request $request)
    {
        $input = $request->all();

        $cliente = \App\Models\Cliente::create([
            'nombre' => $input['nombre'],
            'apellido' => $input['apellido'],
            'direccion' => $input['direccion'],
            'ciudad' => $input['ciudad'] ?? null,
            'coordenadas' => $input['coordenadas'],
            'numero_contacto' => $input['numero_contacto'],
            'nota' => $input['nota'],
            'email' => $input['email'],
            'tipo_cliente' => $input['tipo_cliente'],
            'rut' => $input['rut'],
            'estado' => $input['estado'],
            'id_concession' => auth()->user()->id_concession
        ]);

        return redirect()->route('clientes.index')
                        ->with('success', 'Cliente creado exitosamente.');
    }

    public function show(Cliente $cliente)
    {
        abort_if($cliente->id_concession !== auth()->user()->id_concession, 403);
        return view('clientes.show', compact('cliente'));
    }

    public function edit(Cliente $cliente)
    {
        abort_if($cliente->id_concession !== auth()->user()->id_concession, 403);
        return view('clientes.edit', compact('cliente'));
    }

    public function update(Request $request, Cliente $cliente)
    {
        abort_if($cliente->id_concession !== auth()->user()->id_concession, 403);
        $input = $request->all();
        $cliente = \App\Models\Cliente::find($cliente->id);


        $cliente->nombre = $input['nombre'];
        $cliente->apellido = $input['apellido'];
        $cliente->direccion = $input['direccion'];
        $cliente->ciudad = $input['ciudad'] ?? null;
        $cliente->coordenadas = $input['coordenadas'];
        $cliente->numero_contacto = $input['numero_contacto'];
        $cliente->nota = $input['nota'];
        $cliente->email = $input['email'];
        $cliente->tipo_cliente = $input['tipo_cliente'];
        $cliente->rut = $input['rut'];
        $cliente->estado = $input['estado'];
        $cliente->save();

        return redirect()->route('clientes.index')
                        ->with('success', 'Cliente actualizado exitosamente.');
    }

    public function destroy(Cliente $cliente)
    {
        abort_if($cliente->id_concession !== auth()->user()->id_concession, 403);
        $cliente->delete();

        return redirect()->route('clientes.index')
                        ->with('success', 'Cliente eliminado exitosamente.');
    }

    public function updateCoordenadas(Request $request, Cliente $cliente)
    {
        abort_if($cliente->id_concession !== auth()->user()->id_concession, 403);
        $request->validate([
            'coordenadas' => 'required|string|max:255',
        ]);

        $cliente->coordenadas = $request->coordenadas;
        $cliente->save();

        \App\Models\Log::create([
            'content' => "Coordenadas actualizadas para cliente {$cliente->nombre} {$cliente->apellido}: {$request->coordenadas}",
            'activity' => 'Edición',
            'id_user' => auth()->user()->id,
            'id_concession' => auth()->user()->id_concession
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Ubicación asociada correctamente.',
            'coordenadas' => $cliente->coordenadas,
        ]);
    }
}