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
        $clientes = Cliente::query();

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
            'coordenadas' => $input['coordenadas'],
            'numero_contacto' => $input['numero_contacto'],
            'nota' => $input['nota'],
            'email' => $input['email'],
            'tipo_cliente' => $input['tipo_cliente'],
            'rut' => $input['rut'],
            'estado' => $input['estado']
        ]);

        return redirect()->route('clientes.index')
                        ->with('success', 'Cliente creado exitosamente.');
    }

    public function show(Cliente $cliente)
    {
        return view('clientes.show', compact('cliente'));
    }

    public function edit(Cliente $cliente)
    {
        return view('clientes.edit', compact('cliente'));
    }

    public function update(Request $request, Cliente $cliente)
    {
        $input = $request->all();
        $cliente = \App\Models\Cliente::find($cliente->id);


        $cliente->nombre = $input['nombre'];
        $cliente->apellido = $input['apellido'];
        $cliente->direccion = $input['direccion'];
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
        $cliente->delete();

        return redirect()->route('clientes.index')
                        ->with('success', 'Cliente eliminado exitosamente.');
    }
}