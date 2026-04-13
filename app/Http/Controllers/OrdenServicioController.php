<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\OrdenServicio;
use App\Models\OrdenServicioDetalle;
use App\Models\Cliente;
use App\Models\Artefacto;
use App\Models\Tecnico;
use App\Models\Product;
use App\Models\Servicio;
use Flash;
use DB;

class OrdenServicioController extends Controller
{
    public function index()
    {
        $ordenes = OrdenServicio::with(['cliente', 'tecnico'])->get();
        return view('ordenes_servicio.index', compact('ordenes'));
    }

    public function create()
    {
        $clientes = Cliente::where('estado', true)->get();
        $artefactos = Artefacto::where('estado', true)->get();
        $tecnicos = Tecnico::all();
        $productos = Product::all();
        $servicios = Servicio::where('estado', true)->get();
        
        return view('ordenes_servicio.create', compact('clientes', 'artefactos', 'tecnicos', 'productos', 'servicios'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'numero' => 'required|string|unique:ordenes_servicio',
            'tipo_servicio' => 'required|in:mantenimiento,reparacion,instalacion,garantia',
            'cliente_id' => 'required|exists:clientes,id',
            'descripcion_falla' => 'required|string',
            'tipo_atencion' => 'required|in:taller,terreno',
            'detalles' => 'required|array|min:1',
            'detalles.*.tipo' => 'required|in:producto,servicio',
            'detalles.*.id' => 'required|integer',
            'detalles.*.cantidad' => 'required|integer|min:1',
            'detalles.*.precio' => 'required|numeric|min:0'
        ]);

        try {
            DB::beginTransaction();

            $orden = new OrdenServicio();
            $orden->numero = $request->numero;
            $orden->folio_garantia = $request->folio_garantia;
            $orden->tipo_servicio = $request->tipo_servicio;
            $orden->fecha_orden = now();
            $orden->fecha_visita = $request->fecha_visita;
            $orden->cliente_id = $request->cliente_id;
            $orden->artefacto_id = $request->artefacto_id;
            $orden->descripcion_falla = $request->descripcion_falla;
            $orden->observaciones = $request->observaciones;
            $orden->tipo_atencion = $request->tipo_atencion;
            $orden->valor_visita = $request->valor_visita;
            $orden->tecnico_id = $request->tecnico_id;
            $orden->estado = 'pendiente';
            $orden->save();

            $costoTotal = 0;
            foreach ($request->detalles as $detalle) {
                $ordenDetalle = new OrdenServicioDetalle();
                $ordenDetalle->orden_servicio_id = $orden->id;
                $ordenDetalle->cantidad = $detalle['cantidad'];
                $ordenDetalle->precio_unitario = $detalle['precio'];
                $ordenDetalle->subtotal = $detalle['cantidad'] * $detalle['precio'];
                $ordenDetalle->nota = $detalle['nota'] ?? null;

                if ($detalle['tipo'] === 'producto') {
                    $ordenDetalle->producto_id = $detalle['id'];
                } else {
                    $ordenDetalle->servicio_id = $detalle['id'];
                }

                $ordenDetalle->save();
                $costoTotal += $ordenDetalle->subtotal;
            }

            $orden->costo_total = $costoTotal + ($orden->valor_visita ?? 0);
            $orden->save();

            DB::commit();
            Flash::success('Orden de servicio creada exitosamente.');
            return redirect()->route('ordenes_servicio.index');

        } catch (\Exception $e) {
            DB::rollback();
            Flash::error('Error al crear la orden de servicio.');
            return back()->withInput();
        }
    }

    public function show($id)
    {
        $orden = OrdenServicio::with(['cliente', 'artefacto', 'tecnico', 'detalles.producto', 'detalles.servicio'])->findOrFail($id);
        return view('ordenes_servicio.show', compact('orden'));
    }

    public function edit($id)
    {
        $orden = OrdenServicio::with('detalles')->findOrFail($id);
        $clientes = Cliente::where('estado', true)->get();
        $artefactos = Artefacto::where('estado', true)->get();
        $tecnicos = Tecnico::all();
        $productos = Product::all();
        $servicios = Servicio::where('estado', true)->get();
        
        return view('ordenes_servicio.edit', compact('orden', 'clientes', 'artefactos', 'tecnicos', 'productos', 'servicios'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'numero' => 'required|string|unique:ordenes_servicio,numero,' . $id,
            'tipo_servicio' => 'required|in:mantenimiento,reparacion,instalacion,garantia',
            'cliente_id' => 'required|exists:clientes,id',
            'descripcion_falla' => 'required|string',
            'tipo_atencion' => 'required|in:taller,terreno',
            'estado' => 'required|in:pendiente,en_progreso,finalizada,cancelada'
        ]);

        try {
            DB::beginTransaction();

            $orden = OrdenServicio::findOrFail($id);
            $orden->numero = $request->numero;
            $orden->folio_garantia = $request->folio_garantia;
            $orden->tipo_servicio = $request->tipo_servicio;
            $orden->fecha_visita = $request->fecha_visita;
            $orden->cliente_id = $request->cliente_id;
            $orden->artefacto_id = $request->artefacto_id;
            $orden->descripcion_falla = $request->descripcion_falla;
            $orden->observaciones = $request->observaciones;
            $orden->tipo_atencion = $request->tipo_atencion;
            $orden->valor_visita = $request->valor_visita;
            $orden->tecnico_id = $request->tecnico_id;
            $orden->estado = $request->estado;
            $orden->save();

            DB::commit();
            Flash::success('Orden de servicio actualizada exitosamente.');
            return redirect()->route('ordenes_servicio.index');

        } catch (\Exception $e) {
            DB::rollback();
            Flash::error('Error al actualizar la orden de servicio.');
            return back()->withInput();
        }
    }

    public function destroy($id)
    {
        try {
            $orden = OrdenServicio::findOrFail($id);
            $orden->delete();
            
            Flash::success('Orden de servicio eliminada exitosamente.');
            return redirect()->route('ordenes_servicio.index');
        } catch (\Exception $e) {
            Flash::error('Error al eliminar la orden de servicio.');
            return back();
        }
    }


}