<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tecnico extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'nombre',
        'especialidad',
        'telefono_contacto',
        'email_contacto',
        'zona_cobertura',
        'certificaciones',
        'disponibilidad',
        'nota'
    ];

    protected $casts = [
        'disponibilidad' => 'string'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function ordenesServicio()
    {
        return $this->hasMany(OrdenServicio::class);
    }
}
