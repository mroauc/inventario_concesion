<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MovimientoCaja extends Model
{
    use HasFactory;

    protected $table = 'movimientos_caja';

    protected $fillable = [
        'caja_id',
        'id_concession',
        'fecha',
        'tipo_movimiento',
        'medio',
        'monto',
        'detalle',
        'anulado',
        'usuario_id',
    ];

    protected $casts = [
        'fecha'   => 'date',
        'monto'   => 'decimal:2',
        'anulado' => 'boolean',
    ];

    public function caja()
    {
        return $this->belongsTo(CajaDiaria::class, 'caja_id');
    }

    public function usuario()
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }

    public function concession()
    {
        return $this->belongsTo(Concession::class, 'id_concession');
    }

    // Labels legibles para la vista
    public function getTipoLabel(): string
    {
        return $this->tipo_movimiento === 'ingreso' ? 'Ingreso' : 'Egreso';
    }

    public function getMedioLabel(): string
    {
        return match($this->medio) {
            'efectivo'                    => 'Efectivo',
            'credito_debito'              => 'Crédito/Débito',
            'transferencia'               => 'Transferencia',
            'tecnoelectro'                => 'Tecnoelectro',
            'deposito_banco'              => 'Depósito Banco',
            'deposito_banco_tecnoelectro' => 'Depósito Banco Tecnoelectro',
            default                       => $this->medio,
        };
    }

    public function esTecnoelectro(): bool
    {
        return in_array($this->medio, ['tecnoelectro', 'deposito_banco_tecnoelectro']);
    }
}
