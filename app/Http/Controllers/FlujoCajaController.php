<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\CajaDiaria;
use App\Models\MovimientoCaja;
use Carbon\Carbon;
use Flash;
use DB;

class FlujoCajaController extends Controller
{
    /**
     * Vista principal. Carga (o crea) la caja del día solicitado.
     */
    public function index(Request $request)
    {
        $fecha = $request->filled('fecha')
            ? Carbon::parse($request->input('fecha'))
            : Carbon::now()->startOfDay();

        $idConcession = auth()->user()->id_concession;

        $caja = $this->obtenerOCrearCaja($idConcession, $fecha);

        $movimientos = MovimientoCaja::where('caja_id', $caja->id)
            ->orderBy('created_at', 'asc')
            ->get();

        $totales = $this->calcularTotales($caja);

        return view('flujo_caja.index', compact('caja', 'movimientos', 'totales', 'fecha'));
    }

    /**
     * AJAX: carga datos de un día distinto al cambiar el selector de fecha.
     */
    public function cargarDia(Request $request)
    {
        $request->validate(['fecha' => 'required|date']);

        $fecha        = Carbon::parse($request->input('fecha'));
        $idConcession = auth()->user()->id_concession;

        $caja = $this->obtenerOCrearCaja($idConcession, $fecha);

        $movimientos = MovimientoCaja::where('caja_id', $caja->id)
            ->orderBy('created_at', 'asc')
            ->get();

        $totales = $this->calcularTotales($caja);

        return response()->json([
            'caja'        => $caja,
            'movimientos' => $movimientos->map(fn($m) => $this->formatMovimiento($m)),
            'totales'     => $totales,
        ]);
    }

    /**
     * Registrar un movimiento de caja.
     */
    public function registrarMovimiento(Request $request)
    {
        $request->validate([
            'caja_id'        => 'required|exists:cajas_diarias,id',
            'tipo_movimiento' => 'required|in:ingreso,egreso',
            'medio'          => 'required|in:efectivo,credito_debito,transferencia,tecnoelectro,deposito_banco,deposito_banco_tecnoelectro',
            'monto'          => 'required|numeric|min:0.01',
            'detalle'        => 'required|string|max:255',
        ]);

        $caja = CajaDiaria::findOrFail($request->caja_id);

        if (!$caja->isAbierta()) {
            return response()->json(['error' => 'La caja está cerrada. No se pueden registrar movimientos.'], 422);
        }

        if ($caja->id_concession !== auth()->user()->id_concession) {
            abort(403);
        }

        try {
            DB::beginTransaction();

            $movimiento = MovimientoCaja::create([
                'caja_id'         => $caja->id,
                'id_concession'   => $caja->id_concession,
                'fecha'           => $caja->fecha,
                'tipo_movimiento' => $request->tipo_movimiento,
                'medio'           => $request->medio,
                'monto'           => $request->monto,
                'detalle'         => $request->detalle,
                'anulado'         => false,
                'usuario_id'      => auth()->id(),
            ]);

            // Actualizar deposito_dia y deposito_tecnoelectro en cajas_diarias
            $this->sincronizarDepositos($caja);

            \App\Models\Log::create([
                'content'      => "Movimiento de caja registrado: {$movimiento->getTipoLabel()} / {$movimiento->getMedioLabel()} / $" . number_format($movimiento->monto, 2),
                'activity'     => 'Creación',
                'id_user'      => auth()->id(),
                'id_concession' => auth()->user()->id_concession,
            ]);

            DB::commit();

            return response()->json([
                'success'    => true,
                'movimiento' => $this->formatMovimiento($movimiento),
                'totales'    => $this->calcularTotales($caja->fresh()),
            ]);
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json(['error' => 'Error al registrar movimiento: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Anular un movimiento (soft: campo anulado = true).
     */
    public function anularMovimiento(Request $request, MovimientoCaja $movimiento)
    {
        $caja = $movimiento->caja;

        if (!$caja->isAbierta()) {
            return response()->json(['error' => 'La caja está cerrada. No se pueden anular movimientos.'], 422);
        }

        if ($caja->id_concession !== auth()->user()->id_concession) {
            abort(403);
        }

        if ($movimiento->anulado) {
            return response()->json(['error' => 'El movimiento ya está anulado.'], 422);
        }

        try {
            DB::beginTransaction();

            $movimiento->update(['anulado' => true]);

            $this->sincronizarDepositos($caja);

            \App\Models\Log::create([
                'content'      => "Movimiento de caja anulado (ID #{$movimiento->id}): {$movimiento->getTipoLabel()} / {$movimiento->getMedioLabel()} / $" . number_format($movimiento->monto, 2),
                'activity'     => 'Eliminación',
                'id_user'      => auth()->id(),
                'id_concession' => auth()->user()->id_concession,
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'totales' => $this->calcularTotales($caja->fresh()),
            ]);
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json(['error' => 'Error al anular movimiento: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Actualizar apertura_caja y/o apertura_tecnoelectro (campos editables).
     */
    public function actualizarAperturas(Request $request, CajaDiaria $caja)
    {
        if ($caja->id_concession !== auth()->user()->id_concession) {
            abort(403);
        }

        if (!$caja->isAbierta()) {
            return response()->json(['error' => 'La caja está cerrada.'], 422);
        }

        $request->validate([
            'apertura_caja'         => 'nullable|numeric|min:0',
            'apertura_tecnoelectro' => 'nullable|numeric|min:0',
        ]);

        try {
            DB::beginTransaction();

            $cambios = [];
            if ($request->filled('apertura_caja')) {
                $cambios['apertura_caja'] = $request->apertura_caja;
            }
            if ($request->filled('apertura_tecnoelectro')) {
                $cambios['apertura_tecnoelectro'] = $request->apertura_tecnoelectro;
            }

            if (!empty($cambios)) {
                $caja->update($cambios);

                \App\Models\Log::create([
                    'content'      => 'Apertura de caja actualizada para el día ' . $caja->fecha->format('d/m/Y'),
                    'activity'     => 'Edición',
                    'id_user'      => auth()->id(),
                    'id_concession' => auth()->user()->id_concession,
                ]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'totales' => $this->calcularTotales($caja->fresh()),
            ]);
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json(['error' => 'Error al actualizar apertura: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Cerrar la caja del día: persiste los cierres calculados.
     */
    public function cerrarCaja(Request $request, CajaDiaria $caja)
    {
        if ($caja->id_concession !== auth()->user()->id_concession) {
            abort(403);
        }

        if (!$caja->isAbierta()) {
            return response()->json(['error' => 'La caja ya está cerrada.'], 422);
        }

        try {
            DB::beginTransaction();

            $cierreCaja         = $caja->calcularCierreCaja();
            $cierreTecnoelectro = $caja->calcularCierreTecnoelectro();

            $caja->update([
                'estado'                => 'cerrada',
                'cierre_caja'           => $cierreCaja,
                'cierre_tecnoelectro'   => $cierreTecnoelectro,
                'deposito_dia'          => $caja->totalDepositoBanco(),
                'deposito_tecnoelectro' => $caja->totalDepositoBancoTecnoelectro(),
            ]);

            // Propagar aperturas al día hábil siguiente: actualizar si existe, crear si no existe
            $fechaSiguiente = CajaDiaria::diaHabilSiguiente($caja->fecha);
            $cajaSiguiente  = CajaDiaria::where('id_concession', $caja->id_concession)
                ->where('fecha', $fechaSiguiente->toDateString())
                ->first();

            if ($cajaSiguiente) {
                $cajaSiguiente->update([
                    'apertura_caja'         => $cierreCaja,
                    'apertura_tecnoelectro' => $cierreTecnoelectro,
                ]);
            } else {
                $test = CajaDiaria::create([
                    'id_concession'         => $caja->id_concession,
                    'fecha'                 => $fechaSiguiente->toDateString(),
                    'estado'                => 'abierta',
                    'apertura_caja'         => $cierreCaja,
                    'apertura_tecnoelectro' => $cierreTecnoelectro,
                    'deposito_dia'          => 0,
                    'cierre_caja'           => 0,
                    'deposito_tecnoelectro' => 0,
                    'cierre_tecnoelectro'   => 0,
                ]);
            }

            \App\Models\Log::create([
                'content'      => 'Caja cerrada para el día ' . $caja->fecha->format('d/m/Y') . '. Cierre: $' . number_format($cierreCaja, 2),
                'activity'     => 'Edición',
                'id_user'      => auth()->id(),
                'id_concession' => auth()->user()->id_concession,
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'caja'    => $caja->fresh(),
                'totales' => $this->calcularTotales($caja->fresh()),
            ]);
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json(['error' => 'Error al cerrar caja: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Reabrir la caja del día.
     */
    public function reabrirCaja(Request $request, CajaDiaria $caja)
    {
        if (!auth()->user()->can('flujo_caja.reabrir')) {
            abort(403, 'No tienes permiso para reabrir una caja cerrada.');
        }

        if ($caja->id_concession !== auth()->user()->id_concession) {
            abort(403);
        }

        if ($caja->isAbierta()) {
            return response()->json(['error' => 'La caja ya está abierta.'], 422);
        }

        try {
            DB::beginTransaction();

            $caja->update(['estado' => 'abierta']);

            \App\Models\Log::create([
                'content'      => 'Caja reabierta para el día ' . $caja->fecha->format('d/m/Y'),
                'activity'     => 'Edición',
                'id_user'      => auth()->id(),
                'id_concession' => auth()->user()->id_concession,
            ]);

            DB::commit();

            return response()->json(['success' => true, 'caja' => $caja->fresh()]);
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json(['error' => 'Error al reabrir caja: ' . $e->getMessage()], 500);
        }
    }

    // -------------------------------------------------------------------------
    // Helpers privados
    // -------------------------------------------------------------------------

    /**
     * Obtiene la caja del día o la crea con aperturas desde el día hábil anterior.
     */
    private function obtenerOCrearCaja(int $idConcession, Carbon $fecha): CajaDiaria
    {
        $caja = CajaDiaria::where('id_concession', $idConcession)
            ->where('fecha', $fecha->toDateString())
            ->first();

        if (!$caja) {
            $caja = CajaDiaria::create([
                'id_concession'         => $idConcession,
                'fecha'                 => $fecha->toDateString(),
                'estado'                => 'abierta',
                'apertura_caja'         => CajaDiaria::aperturaDesdeAnterior($idConcession, $fecha),
                'apertura_tecnoelectro' => CajaDiaria::aperturaTecnoelectroDesdeAnterior($idConcession, $fecha),
                'deposito_dia'          => 0,
                'cierre_caja'           => 0,
                'deposito_tecnoelectro' => 0,
                'cierre_tecnoelectro'   => 0,
            ]);
        }

        return $caja;
    }

    /**
     * Recalcula y persiste deposito_dia y deposito_tecnoelectro desde los movimientos activos.
     */
    private function sincronizarDepositos(CajaDiaria $caja): void
    {
        $caja->update([
            'deposito_dia'          => $caja->totalDepositoBanco(),
            'deposito_tecnoelectro' => $caja->totalDepositoBancoTecnoelectro(),
        ]);
    }

    /**
     * Construye el array de totales para enviar a la vista / AJAX.
     */
    private function calcularTotales(CajaDiaria $caja): array
    {
        $movimientos = $caja->movimientos()->where('anulado', false)->get();

        $sumar = fn($tipo, $medio) => (float) $movimientos
            ->where('tipo_movimiento', $tipo)
            ->where('medio', $medio)
            ->sum('monto');

        return [
            // Por medio
            'ingreso_efectivo'       => $sumar('ingreso', 'efectivo'),
            'egreso_efectivo'        => $sumar('egreso', 'efectivo'),
            'ingreso_credito_debito' => $sumar('ingreso', 'credito_debito'),
            'egreso_credito_debito'  => $sumar('egreso', 'credito_debito'),
            'ingreso_transferencia'  => $sumar('ingreso', 'transferencia'),
            'egreso_transferencia'   => $sumar('egreso', 'transferencia'),
            'ingreso_tecnoelectro'   => $sumar('ingreso', 'tecnoelectro'),
            'egreso_tecnoelectro'    => $sumar('egreso', 'tecnoelectro'),
            'deposito_banco'              => (float) $movimientos->where('medio', 'deposito_banco')->sum('monto'),
            'deposito_banco_tecnoelectro' => (float) $movimientos->where('medio', 'deposito_banco_tecnoelectro')->sum('monto'),

            // Totales caja chica (efectivo + crédito/débito + transferencia + depósito banco)
            'total_ingresos' => (float) $movimientos->where('tipo_movimiento', 'ingreso')
                ->whereIn('medio', ['efectivo', 'credito_debito', 'transferencia'])->sum('monto'),
            'total_egresos'  => (float) $movimientos->where('tipo_movimiento', 'egreso')
                ->whereIn('medio', ['efectivo', 'credito_debito', 'transferencia'])->sum('monto')
                + (float) $movimientos->where('medio', 'deposito_banco')->sum('monto'),

            // Totales Tecnoelectro
            'total_ingresos_tecno' => (float) $movimientos->where('tipo_movimiento', 'ingreso')
                ->where('medio', 'tecnoelectro')->sum('monto'),
            'total_egresos_tecno'  => (float) $movimientos->where('tipo_movimiento', 'egreso')
                ->where('medio', 'tecnoelectro')->sum('monto')
                + (float) $movimientos->where('medio', 'deposito_banco_tecnoelectro')->sum('monto'),

            // Cierres calculados en tiempo real
            'cierre_caja'         => $caja->calcularCierreCaja(),
            'cierre_tecnoelectro' => $caja->calcularCierreTecnoelectro(),

            // Aperturas y depósitos
            'apertura_caja'         => (float) $caja->apertura_caja,
            'apertura_tecnoelectro' => (float) $caja->apertura_tecnoelectro,
            'deposito_dia'                => (float) $caja->deposito_dia,
            'deposito_tecnoelectro'       => (float) $caja->deposito_tecnoelectro,
            'deposito_banco_tecnoelectro' => (float) $caja->totalDepositoBancoTecnoelectro(),
        ];
    }

    /**
     * Formatea un movimiento para respuestas JSON.
     */
    private function formatMovimiento(MovimientoCaja $m): array
    {
        return [
            'id'              => $m->id,
            'tipo_movimiento' => $m->tipo_movimiento,
            'tipo_label'      => $m->getTipoLabel(),
            'medio'           => $m->medio,
            'medio_label'     => $m->getMedioLabel(),
            'monto'           => (float) $m->monto,
            'detalle'         => $m->detalle,
            'anulado'         => $m->anulado,
            'usuario'         => $m->usuario->name ?? '—',
            'created_at'      => $m->created_at->timezone(config('app.timezone'))->format('H:i'),
        ];
    }
}
