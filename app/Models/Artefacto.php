<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Artefacto extends Model
{
    use HasFactory;

    protected $fillable = [
        'tipo_artefacto_id',
        'codigo',
        'marca',
        'modelo',
        'descripcion',
        'estado',
        'id_concession',
    ];

    protected $casts = [
        'estado' => 'boolean'
    ];

    public function tipoArtefacto()
    {
        return $this->belongsTo(TipoArtefacto::class);
    }

    public function ordenesServicio()
    {
        return $this->hasMany(OrdenServicio::class);
    }
}