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

    public function index(Request $request)
    {
        $clientes = Cliente::where('id_concession', auth()->user()->id_concession);

        if ($request->filled('search')) {
            $search = $request->get('search');
            $clientes->where(function($query) use ($search) {
                $query->where('nombre', 'like', "%{$search}%")
                      ->orWhere('apellido', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%")
                      ->orWhere('rut', 'like', "%{$search}%");
            });
        }

        $clientes = $clientes->paginate(15);

        return view('clientes.index', compact('clientes'));
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