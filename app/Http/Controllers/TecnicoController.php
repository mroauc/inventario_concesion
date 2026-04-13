<?php

namespace App\Http\Controllers;

use App\Http\Requests\TecnicoRequest;
use App\Models\Tecnico;
use App\Models\User;
use Illuminate\Http\Request;

class TecnicoController extends Controller
{
    public function index()
    {
        $tecnicos = Tecnico::with('user')->paginate(10);
        return view('tecnicos.index', compact('tecnicos'));
    }

    public function create()
    {
        $users = User::whereDoesntHave('tecnico')->get();
        return view('tecnicos.create', compact('users'));
    }

    public function store(TecnicoRequest $request)
    {
        // Tecnico::create($request->validated());
        // Define las reglas de validación.
        $rules = [
            'user_id' => 'nullable|exists:users,id|unique:tecnicos,user_id',
            'nombre' => 'required|string|max:255',
            'especialidad' => 'required|string|max:255',
            'telefono_contacto' => 'nullable|string|max:255',
            'email_contacto' => 'nullable|email|max:255',
            'zona_cobertura' => 'nullable|string|max:255',
            'certificaciones' => 'nullable|string',
            'disponibilidad' => 'required|in:disponible,ocupado,de_baja',
            'nota' => 'nullable|string'
        ];

        // Valida la solicitud utilizando las reglas definidas.
        $validatedData = $request->validate($rules);

        // Crea una nueva instancia del modelo Tecnico.
        $tecnico = new Tecnico();

        // Asigna explícitamente cada atributo con los datos validados.
        // Se usa el operador de anulación de fusión de null (??)
        // para manejar los campos que son opcionales.
        $tecnico->user_id = $validatedData['user_id'] ?? null;
        $tecnico->nombre = $validatedData['nombre'];
        $tecnico->especialidad = $validatedData['especialidad'];
        $tecnico->telefono_contacto = $validatedData['telefono_contacto'] ?? null;
        $tecnico->email_contacto = $validatedData['email_contacto'] ?? null;
        $tecnico->zona_cobertura = $validatedData['zona_cobertura'] ?? null;
        $tecnico->certificaciones = $validatedData['certificaciones'] ?? null;
        $tecnico->disponibilidad = $validatedData['disponibilidad'];
        $tecnico->nota = $validatedData['nota'] ?? null;

        $tecnico->save();

        return redirect()->route('tecnicos.index')->with('success', 'Técnico creado exitosamente.');
    }

    public function show(Tecnico $tecnico)
    {
        $tecnico->load('user');
        return view('tecnicos.show', compact('tecnico'));
    }

    public function edit(Tecnico $tecnico)
    {
        $users = User::whereDoesntHave('tecnico')->orWhere('id', $tecnico->user_id)->get();
        return view('tecnicos.edit', compact('tecnico', 'users'));
    }

    public function update(TecnicoRequest $request, Tecnico $tecnico)
    {
        $tecnico->update($request->validated());
        return redirect()->route('tecnicos.index')->with('success', 'Técnico actualizado exitosamente.');
    }

    public function destroy(Tecnico $tecnico)
    {
        $tecnico->delete();
        return redirect()->route('tecnicos.index')->with('success', 'Técnico eliminado exitosamente.');
    }
}
