<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cliente extends Model
{
    use HasFactory;

    protected $fillable = [
        'nombre',
        'apellido',
        'direccion',
        'coordenadas',
        'numero_contacto',
        'nota',
        'email',
        'tipo_cliente',
        'rut',
        'estado'
    ];

    protected $casts = [
        'estado' => 'boolean'
    ];

    public static $rules = [
        'nombre' => 'required|string|max:255',
        'apellido' => 'required|string|max:255',
        'direccion' => 'required|string|max:255',
        'numero_contacto' => 'required|string|max:255',
        'email' => 'nullable|email|max:255',
        'tipo_cliente' => 'required|in:residencial,empresa,concesion',
        'rut' => 'nullable|string|max:255',
        'estado' => 'boolean'
    ];
}