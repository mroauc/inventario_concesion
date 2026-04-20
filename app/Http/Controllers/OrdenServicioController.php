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
        return view('ordenes_servicio.index');
    }

    public function datatables(Request $request)
    {
        $draw   = $request->input('draw', 1);
        $start  = $request->input('start', 0);
        $length = $request->input('length', 10);
        $search = $request->input('search.value', '');

        $query = OrdenServicio::where('id_concession', auth()->user()->id_concession)
            ->with(['cliente', 'tecnico']);

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('numero', 'like', "%{$search}%")
                  ->orWhere('tipo_servicio', 'like', "%{$search}%")
                  ->orWhere('estado', 'like', "%{$search}%")
                  ->orWhereHas('cliente', function ($q2) use ($search) {
                      $q2->where('nombre', 'like', "%{$search}%")
                         ->orWhere('apellido', 'like', "%{$search}%")
                         ->orWhere('rut', 'like', "%{$search}%");
                  })
                  ->orWhereHas('tecnico', function ($q2) use ($search) {
                      $q2->where('nombre', 'like', "%{$search}%");
                  });
            });
        }

        $total    = OrdenServicio::where('id_concession', auth()->user()->id_concession)->count();
        $filtered = $query->count();

        // Columnas ordenables: 0=numero, 1=cliente, 2=tipo_servicio, 3=fecha_orden, 4=estado, 5=tecnico, 6=costo_total
        $orderCol   = $request->input('order.0.column', 3);
        $orderDir   = $request->input('order.0.dir', 'desc') === 'asc' ? 'asc' : 'desc';
        $columnMap  = [0 => 'numero', 2 => 'tipo_servicio', 3 => 'fecha_orden', 4 => 'estado', 6 => 'costo_total'];
        $sortColumn = $columnMap[$orderCol] ?? 'fecha_orden';
        $query->orderBy($sortColumn, $orderDir);

        $ordenes = $query->skip($start)->take($length)->get();

        $data = $ordenes->map(function ($orden) {
            $cliente = $orden->cliente
                ? (($orden->cliente->rut ? '<small class="text-muted d-block">' . $orden->cliente->rut . '</small>' : '')
                    . e($orden->cliente->nombre) . ' ' . e($orden->cliente->apellido))
                : '-';

            $tipoServicio = '<span class="badge badge-info">' . ucfirst(e($orden->tipo_servicio)) . '</span>';

            $estadoBadge = [
                'pendiente'   => '<span class="badge badge-warning">Pendiente</span>',
                'en_progreso' => '<span class="badge badge-primary">En Progreso</span>',
                'finalizada'  => '<span class="badge badge-success">Finalizada</span>',
                'cancelada'   => '<span class="badge badge-danger">Cancelada</span>',
            ];
            $estado = $estadoBadge[$orden->estado] ?? e($orden->estado);

            $tecnico = $orden->tecnico ? e($orden->tecnico->nombre) : 'Sin asignar';

            $acciones = '
                <div class="btn-group">
                    <a href="' . route('ordenes_servicio.show', $orden->id) . '" class="btn btn-info btn-sm"><i class="fas fa-eye"></i></a>
                    <a href="' . route('ordenes_servicio.edit', $orden->id) . '" class="btn btn-primary btn-sm"><i class="fas fa-edit"></i></a>
                    <form method="POST" action="' . route('ordenes_servicio.destroy', $orden->id) . '" style="display:inline">
                        <input type="hidden" name="_token" value="' . csrf_token() . '">
                        <input type="hidden" name="_method" value="DELETE">
                        <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm(\'¿Está seguro?\')"><i class="fas fa-trash"></i></button>
                    </form>
                </div>';

            return [
                e($orden->numero),
                $cliente,
                $tipoServicio,
                $orden->fecha_orden ? $orden->fecha_orden->format('d/m/Y H:i') : '-',
                $estado,
                $tecnico,
                '$' . number_format($orden->costo_total, 0, ',', '.'),
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
        $clientes = Cliente::where('id_concession', auth()->user()->id_concession)->where('estado', true)->get();
        $artefactos = Artefacto::where('id_concession', auth()->user()->id_concession)
            ->where('estado', true)
            ->with('tipoArtefacto')
            ->orderBy('tipo_artefacto_id')
            ->orderBy('marca')
            ->get();
        $tecnicos = Tecnico::where('id_concession', auth()->user()->id_concession)->get();
        $productos = Product::where('id_concession', auth()->user()->id_concession)->get();
        $servicios = Servicio::where('id_concession', auth()->user()->id_concession)->where('estado', true)->get();

        $proximoNumero = $this->proximoNumeroOrden(auth()->user()->id_concession);

        return view('ordenes_servicio.create', compact('clientes', 'artefactos', 'tecnicos', 'productos', 'servicios', 'proximoNumero'));
    }

    public function store(Request $request)
    {
        $request->validate([
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

            // Bloqueo pesimista sobre la concesión para serializar accesos concurrentes
            $concesion = \App\Models\Concession::where('id', auth()->user()->id_concession)
                ->lockForUpdate()
                ->firstOrFail();

            // Máximo numero existente para esta concesión; si no hay órdenes usa numero_orden_siguiente como base
            $maxNumero = OrdenServicio::where('id_concession', $concesion->id)->max('numero');
            $numeroOrden = $maxNumero !== null
                ? $maxNumero + 1
                : $concesion->numero_orden_siguiente;

            $orden = new OrdenServicio();
            $orden->numero = $numeroOrden;
            $orden->id_concession = auth()->user()->id_concession;
            $orden->folio_garantia = $request->folio_garantia;
            $orden->tipo_asistencia = $request->tipo_asistencia;
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
        $clientes = Cliente::where('id_concession', auth()->user()->id_concession)->where('estado', true)->get();
        $artefactos = Artefacto::where('id_concession', auth()->user()->id_concession)
            ->where('estado', true)
            ->with('tipoArtefacto')
            ->orderBy('tipo_artefacto_id')
            ->orderBy('marca')
            ->get();
        $tecnicos = Tecnico::where('id_concession', auth()->user()->id_concession)->get();
        $productos = Product::where('id_concession', auth()->user()->id_concession)->get();
        $servicios = Servicio::where('id_concession', auth()->user()->id_concession)->where('estado', true)->get();
        
        return view('ordenes_servicio.edit', compact('orden', 'clientes', 'artefactos', 'tecnicos', 'productos', 'servicios'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'cliente_id' => 'required|exists:clientes,id',
            'descripcion_falla' => 'required|string',
            'tipo_atencion' => 'required|in:taller,terreno',
            'estado' => 'required|in:pendiente,en_progreso,finalizada,cancelada'
        ]);

        try {
            DB::beginTransaction();

            $orden = OrdenServicio::findOrFail($id);
            // numero no se modifica: es correlativo asignado al crear
            $orden->folio_garantia = $request->folio_garantia;
            $orden->tipo_asistencia = $request->tipo_asistencia;
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

    /**
     * Retorna los datos de un cliente en JSON para el selector AJAX.
     */
    public function clienteDatos(Cliente $cliente)
    {
        return response()->json([
            'rut'             => $cliente->rut,
            'nombre'          => $cliente->nombre . ' ' . $cliente->apellido,
            'direccion'       => $cliente->direccion,
            'ciudad'          => $cliente->ciudad,
            'numero_contacto' => $cliente->numero_contacto,
            'email'           => $cliente->email,
        ]);
    }

    /**
     * Calcula el próximo número de orden para una concesión.
     * Usa el máximo número existente en ordenes_servicio para esa concesión.
     * Si no existen órdenes, toma numero_orden_siguiente de la tabla concessions.
     */
    private function proximoNumeroOrden(int $idConcession)
    {
        $concesion = \App\Models\Concession::find($idConcession);
        if (!$concesion) {
            return '—';
        }

        $maxNumero = OrdenServicio::where('id_concession', $idConcession)->max('numero');

        return $maxNumero !== null ? $maxNumero + 1 : $concesion->numero_orden_siguiente;
    }
}