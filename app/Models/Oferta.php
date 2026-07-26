<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Oferta extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'nombre',
        'descripcion',
        'precio',
        'vendido',
        'estado',
        'fotos',
        'id_concession',
    ];

    protected $casts = [
        'fotos'   => 'array',
        'vendido' => 'boolean',
        'estado'  => 'boolean',
        'precio'  => 'decimal:2',
    ];

    public function fotoPrincipal(): ?string
    {
        return $this->fotos[0] ?? null;
    }
}
