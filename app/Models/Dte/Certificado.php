<?php

namespace App\Models\Dte;

use App\Models\Concession;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;

class Certificado extends Model
{
    protected $table = 'dte_certificados';

    protected $fillable = [
        'id_concession',
        'rut_titular',
        'nombre_titular',
        'archivo_path',
        'password_cifrada',
        'fecha_vencimiento',
        'activo',
    ];

    protected $casts = [
        'fecha_vencimiento' => 'date',
        'activo' => 'boolean',
    ];

    protected $hidden = [
        'password_cifrada',
    ];

    public function concession()
    {
        return $this->belongsTo(Concession::class, 'id_concession');
    }

    public function setPasswordCifradaAttribute($value)
    {
        $this->attributes['password_cifrada'] = Crypt::encryptString($value);
    }

    public function getPasswordDescifradaAttribute(): string
    {
        return Crypt::decryptString($this->attributes['password_cifrada']);
    }

    public function scopeActivo($query)
    {
        return $query->where('activo', true);
    }

    public function scopeVigente($query)
    {
        return $query->where('fecha_vencimiento', '>', now());
    }

    public function isVigente(): bool
    {
        return $this->fecha_vencimiento->isFuture();
    }

    public function getArchivoCompletoAttribute(): string
    {
        return storage_path('app/' . $this->archivo_path);
    }
}
