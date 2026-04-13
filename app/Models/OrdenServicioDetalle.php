<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrdenServicioDetalle extends Model
{
    use HasFactory;

    protected $table = 'orden_servicio_detalles';

    protected $fillable = [
        'orden_servicio_id',
        'producto_id',
        'servicio_id',
        'cantidad',
        'precio_unitario',
        'subtotal',
        'nota'
    ];

    protected $casts = [
        'precio_unitario' => 'decimal:2',
        'subtotal' => 'decimal:2'
    ];

    public function ordenServicio()
    {
        return $this->belongsTo(OrdenServicio::class);
    }

    public function producto()
    {
        return $this->belongsTo(Product::class);
    }

    public function servicio()
    {
        return $this->belongsTo(Servicio::class);
    }
}