<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Servicio extends Model
{
    use HasFactory;

    protected $fillable = [
        'nombre_servicio',
        'precio',
        'descripcion',
        'duracion_estimada',
        'estado',
        'requiere_repuestos'
    ];

    protected $casts = [
        'precio' => 'decimal:2',
        'estado' => 'boolean',
        'requiere_repuestos' => 'boolean'
    ];

    public static $rules = [
        'nombre_servicio' => 'required|string|max:255',
        'precio' => 'required|numeric|min:0',
        'descripcion' => 'nullable|string',
        'duracion_estimada' => 'nullable|integer|min:1',
        'estado' => 'boolean',
        'requiere_repuestos' => 'boolean'
    ];
}