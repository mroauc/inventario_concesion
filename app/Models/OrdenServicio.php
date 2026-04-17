<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class OrdenServicio extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'ordenes_servicio';

    protected $fillable = [
        'numero',
        'folio_garantia',
        'tipo_servicio',
        'fecha_orden',
        'fecha_visita',
        'cliente_id',
        'artefacto_id',
        'descripcion_falla',
        'observaciones',
        'tipo_atencion',
        'valor_visita',
        'costo_total',
        'tecnico_id',
        'estado',
        'id_concession'
    ];

    protected $casts = [
        'fecha_orden' => 'datetime',
        'fecha_visita' => 'datetime',
        'valor_visita' => 'decimal:2',
        'costo_total' => 'decimal:2'
    ];

    public function cliente()
    {
        return $this->belongsTo(Cliente::class);
    }

    public function artefacto()
    {
        return $this->belongsTo(Artefacto::class);
    }

    public function tecnico()
    {
        return $this->belongsTo(Tecnico::class);
    }

    public function detalles()
    {
        return $this->hasMany(OrdenServicioDetalle::class);
    }
}