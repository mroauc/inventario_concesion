<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class CajaDiaria extends Model
{
    use HasFactory;

    protected $table = 'cajas_diarias';

    protected $fillable = [
        'id_concession',
        'fecha',
        'estado',
        'apertura_caja',
        'deposito_dia',
        'cierre_caja',
        'apertura_tecnoelectro',
        'deposito_tecnoelectro',
        'cierre_tecnoelectro',
    ];

    protected $casts = [
        'fecha'                 => 'date',
        'apertura_caja'         => 'decimal:2',
        'deposito_dia'          => 'decimal:2',
        'cierre_caja'           => 'decimal:2',
        'apertura_tecnoelectro' => 'decimal:2',
        'deposito_tecnoelectro' => 'decimal:2',
        'cierre_tecnoelectro'   => 'decimal:2',
    ];

    public function movimientos()
    {
        return $this->hasMany(MovimientoCaja::class, 'caja_id');
    }

    public function concession()
    {
        return $this->belongsTo(Concession::class, 'id_concession');
    }

    public function isAbierta(): bool
    {
        return $this->estado === 'abierta';
    }

    private const MEDIOS_CAJA_CHICA = ['efectivo', 'credito_debito', 'transferencia'];

    // Suma de ingresos en efectivo no anulados
    public function totalIngresoEfectivo(): float
    {
        return (float) $this->movimientos()
            ->where('tipo_movimiento', 'ingreso')
            ->where('medio', 'efectivo')
            ->where('anulado', false)
            ->sum('monto');
    }

    // Suma de egresos en efectivo no anulados
    public function totalEgresoEfectivo(): float
    {
        return (float) $this->movimientos()
            ->where('tipo_movimiento', 'egreso')
            ->where('medio', 'efectivo')
            ->where('anulado', false)
            ->sum('monto');
    }

    // Suma de todos los ingresos de caja chica (efectivo + crédito/débito + transferencia)
    public function totalIngresoCajaChica(): float
    {
        return (float) $this->movimientos()
            ->where('tipo_movimiento', 'ingreso')
            ->whereIn('medio', self::MEDIOS_CAJA_CHICA)
            ->where('anulado', false)
            ->sum('monto');
    }

    // Suma de todos los egresos de caja chica (efectivo + crédito/débito + transferencia)
    public function totalEgresoCajaChica(): float
    {
        return (float) $this->movimientos()
            ->where('tipo_movimiento', 'egreso')
            ->whereIn('medio', self::MEDIOS_CAJA_CHICA)
            ->where('anulado', false)
            ->sum('monto');
    }

    // Suma de depósitos al banco (caja chica) no anulados
    public function totalDepositoBanco(): float
    {
        return (float) $this->movimientos()
            ->where('medio', 'deposito_banco')
            ->where('anulado', false)
            ->sum('monto');
    }

    // Suma de depósitos al banco Tecnoelectro no anulados
    public function totalDepositoBancoTecnoelectro(): float
    {
        return (float) $this->movimientos()
            ->where('medio', 'deposito_banco_tecnoelectro')
            ->where('anulado', false)
            ->sum('monto');
    }

    // Suma de ingresos Tecnoelectro no anulados
    public function totalIngresoTecnoelectro(): float
    {
        return (float) $this->movimientos()
            ->where('tipo_movimiento', 'ingreso')
            ->where('medio', 'tecnoelectro')
            ->where('anulado', false)
            ->sum('monto');
    }

    // Suma de egresos Tecnoelectro no anulados
    public function totalEgresoTecnoelectro(): float
    {
        return (float) $this->movimientos()
            ->where('tipo_movimiento', 'egreso')
            ->where('medio', 'tecnoelectro')
            ->where('anulado', false)
            ->sum('monto');
    }

    // Cierre caja chica calculado en tiempo real
    public function calcularCierreCaja(): float
    {
        return (float) $this->apertura_caja
            + $this->totalIngresoCajaChica()
            - $this->totalEgresoCajaChica()
            - $this->totalDepositoBanco();
    }

    // Cierre Tecnoelectro calculado en tiempo real
    public function calcularCierreTecnoelectro(): float
    {
        return (float) $this->apertura_tecnoelectro
            + $this->totalIngresoTecnoelectro()
            - $this->totalEgresoTecnoelectro()
            - $this->totalDepositoBancoTecnoelectro();
    }

    // Retorna el día hábil anterior (lunes → sábado anterior)
    public static function diaHabilAnterior(Carbon $fecha): Carbon
    {
        $anterior = $fecha->copy()->subDay();
        while ($anterior->dayOfWeek === Carbon::SUNDAY) {
            $anterior->subDay();
        }
        return $anterior;
    }

    // Retorna el día hábil siguiente (sábado → lunes siguiente)
    public static function diaHabilSiguiente(Carbon $fecha): Carbon
    {
        $siguiente = $fecha->copy()->addDay();
        while ($siguiente->dayOfWeek === Carbon::SUNDAY) {
            $siguiente->addDay();
        }
        return $siguiente;
    }

    // Busca el cierre de caja chica del día hábil anterior para una concesión
    public static function aperturaDesdeAnterior(int $idConcession, Carbon $fechaActual): float
    {
        $diaAnterior = self::diaHabilAnterior($fechaActual);
        $cajaAnterior = self::where('id_concession', $idConcession)
            ->where('fecha', $diaAnterior->toDateString())
            ->first();
        return $cajaAnterior ? (float) $cajaAnterior->cierre_caja : 0.0;
    }

    // Busca el cierre Tecnoelectro del día hábil anterior para una concesión
    public static function aperturaTecnoelectroDesdeAnterior(int $idConcession, Carbon $fechaActual): float
    {
        $diaAnterior = self::diaHabilAnterior($fechaActual);
        $cajaAnterior = self::where('id_concession', $idConcession)
            ->where('fecha', $diaAnterior->toDateString())
            ->first();
        return $cajaAnterior ? (float) $cajaAnterior->cierre_tecnoelectro : 0.0;
    }
}
