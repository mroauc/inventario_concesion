<?php

namespace App\Http\Controllers\Dte;

use App\Http\Controllers\Controller;
use App\Models\Dte\ConfiguracionFiscal;
use Illuminate\Http\Request;
use Laracasts\Flash\Flash;

class ConfiguracionFiscalController extends Controller
{
    public function index()
    {
        $concessionId = auth()->user()->id_concession;
        $config = ConfiguracionFiscal::where('id_concession', $concessionId)->first();

        if ($config) {
            return redirect()->route('dte.configuracion.show', $config);
        }

        return redirect()->route('dte.configuracion.create');
    }

    public function create()
    {
        $concessionId = auth()->user()->id_concession;
        $existente = ConfiguracionFiscal::where('id_concession', $concessionId)->exists();

        if ($existente) {
            Flash::warning('Ya existe una configuracion fiscal para esta concesion.');
            return redirect()->route('dte.configuracion.index');
        }

        $configuracion = new ConfiguracionFiscal();
        return view('dte.configuracion.create', compact('configuracion'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'rut_empresa' => 'required|string|max:12|regex:/^\d{1,8}-[\dkK]$/',
            'razon_social' => 'required|string|max:100',
            'giro' => 'required|string|max:80',
            'actividad_economica' => 'required|integer|min:1',
            'direccion' => 'required|string|max:70',
            'comuna' => 'required|string|max:20',
            'ciudad' => 'required|string|max:20',
            'telefono' => 'nullable|string|max:20',
            'email_dte' => 'nullable|email|max:100',
            'sucursal_sii' => 'nullable|integer|min:0',
            'fecha_resolucion' => 'required|date',
            'numero_resolucion' => 'required|integer|min:0',
            'ambiente' => 'required|in:certificacion,produccion',
        ]);

        $concessionId = auth()->user()->id_concession;

        $existente = ConfiguracionFiscal::where('id_concession', $concessionId)->exists();
        if ($existente) {
            Flash::error('Ya existe una configuracion fiscal para esta concesion.');
            return redirect()->route('dte.configuracion.index');
        }

        $config = ConfiguracionFiscal::create(array_merge(
            $request->only([
                'rut_empresa', 'razon_social', 'giro', 'actividad_economica',
                'direccion', 'comuna', 'ciudad', 'telefono', 'email_dte',
                'sucursal_sii', 'fecha_resolucion', 'numero_resolucion', 'ambiente',
            ]),
            ['id_concession' => $concessionId]
        ));

        Flash::success('Configuracion fiscal creada correctamente.');
        return redirect()->route('dte.configuracion.show', $config);
    }

    public function show(ConfiguracionFiscal $configuracion)
    {
        if ($configuracion->id_concession !== auth()->user()->id_concession) {
            abort(403);
        }

        return view('dte.configuracion.show', compact('configuracion'));
    }

    public function edit(ConfiguracionFiscal $configuracion)
    {
        if ($configuracion->id_concession !== auth()->user()->id_concession) {
            abort(403);
        }

        return view('dte.configuracion.edit', compact('configuracion'));
    }

    public function update(Request $request, ConfiguracionFiscal $configuracion)
    {
        if ($configuracion->id_concession !== auth()->user()->id_concession) {
            abort(403);
        }

        $request->validate([
            'rut_empresa' => 'required|string|max:12|regex:/^\d{1,8}-[\dkK]$/',
            'razon_social' => 'required|string|max:100',
            'giro' => 'required|string|max:80',
            'actividad_economica' => 'required|integer|min:1',
            'direccion' => 'required|string|max:70',
            'comuna' => 'required|string|max:20',
            'ciudad' => 'required|string|max:20',
            'telefono' => 'nullable|string|max:20',
            'email_dte' => 'nullable|email|max:100',
            'sucursal_sii' => 'nullable|integer|min:0',
            'fecha_resolucion' => 'required|date',
            'numero_resolucion' => 'required|integer|min:0',
            'ambiente' => 'required|in:certificacion,produccion',
            'activa' => 'nullable|boolean',
        ]);

        $configuracion->update($request->only([
            'rut_empresa', 'razon_social', 'giro', 'actividad_economica',
            'direccion', 'comuna', 'ciudad', 'telefono', 'email_dte',
            'sucursal_sii', 'fecha_resolucion', 'numero_resolucion', 'ambiente', 'activa',
        ]));

        Flash::success('Configuracion fiscal actualizada correctamente.');
        return redirect()->route('dte.configuracion.show', $configuracion);
    }
}
