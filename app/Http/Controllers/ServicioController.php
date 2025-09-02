<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateServicioRequest;
use App\Http\Requests\UpdateServicioRequest;
use App\Models\Servicio;
use Illuminate\Http\Request;

class ServicioController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Request $request)
    {
        $servicios = Servicio::query();

        if ($request->filled('search')) {
            $search = $request->get('search');
            $servicios->where(function($query) use ($search) {
                $query->where('nombre_servicio', 'like', "%{$search}%")
                      ->orWhere('descripcion', 'like', "%{$search}%");
            });
        }

        $servicios = $servicios->paginate(15);

        return view('servicios.index', compact('servicios'));
    }

    public function create()
    {
        return view('servicios.create');
    }

    public function store(CreateServicioRequest $request)
    {
        Servicio::create($request->validated());

        return redirect()->route('servicios.index')
                        ->with('success', 'Servicio creado exitosamente.');
    }

    public function show(Servicio $servicio)
    {
        return view('servicios.show', compact('servicio'));
    }

    public function edit(Servicio $servicio)
    {
        return view('servicios.edit', compact('servicio'));
    }

    public function update(UpdateServicioRequest $request, Servicio $servicio)
    {
        $servicio->update($request->validated());

        return redirect()->route('servicios.index')
                        ->with('success', 'Servicio actualizado exitosamente.');
    }

    public function destroy(Servicio $servicio)
    {
        $servicio->delete();

        return redirect()->route('servicios.index')
                        ->with('success', 'Servicio eliminado exitosamente.');
    }
}