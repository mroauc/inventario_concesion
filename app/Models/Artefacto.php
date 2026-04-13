<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Artefacto extends Model
{
    use HasFactory;

    protected $fillable = [
        'nombre',
        'marca',
        'modelo',
        'descripcion',
        'estado'
    ];

    protected $casts = [
        'estado' => 'boolean'
    ];

    public function ordenesServicio()
    {
        return $this->hasMany(OrdenServicio::class);
    }
}