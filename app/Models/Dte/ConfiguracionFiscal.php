<?php

namespace App\Models\Dte;

use App\Models\Concession;
use Illuminate\Database\Eloquent\Model;

class ConfiguracionFiscal extends Model
{
    protected $table = 'configuracion_fiscal';

    protected $fillable = [
        'id_concession',
        'rut_empresa',
        'razon_social',
        'giro',
        'actividad_economica',
        'direccion',
        'comuna',
        'ciudad',
        'telefono',
        'email_dte',
        'sucursal_sii',
        'fecha_resolucion',
        'numero_resolucion',
        'ambiente',
        'activa',
    ];

    protected $casts = [
        'actividad_economica' => 'integer',
        'sucursal_sii' => 'integer',
        'numero_resolucion' => 'integer',
        'fecha_resolucion' => 'date',
        'activa' => 'boolean',
    ];

    public function concession()
    {
        return $this->belongsTo(Concession::class, 'id_concession');
    }

    public function certificadoActivo()
    {
        return $this->hasOneThrough(
            Certificado::class,
            Concession::class,
            'id',
            'id_concession',
            'id_concession',
            'id'
        )->where('dte_certificados.activo', true);
    }

    public function scopeActiva($query)
    {
        return $query->where('activa', true);
    }

    public function isProduccion(): bool
    {
        return $this->ambiente === 'produccion';
    }

    public function getIdkEsperadoAttribute(): int
    {
        return $this->isProduccion() ? 300 : 100;
    }
}
