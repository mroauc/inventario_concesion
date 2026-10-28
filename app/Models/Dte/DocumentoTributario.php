<?php

namespace App\Models\Dte;

use App\Models\Concession;
use App\Models\OrdenServicio;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class DocumentoTributario extends Model
{
    use SoftDeletes;

    protected $table = 'documentos_tributarios';

    protected $fillable = [
        'id_concession',
        'tipo_documento',
        'folio',
        'fecha_emision',
        'rut_emisor',
        'rut_receptor',
        'razon_social_receptor',
        'monto_neto',
        'monto_iva',
        'tasa_iva',
        'monto_exento',
        'monto_total',
        'xml_documento',
        'xml_sobre',
        'ted_xml',
        'estado',
        'track_id',
        'respuesta_sii',
        'envio_intentos',
        'ultimo_envio_at',
        'orden_servicio_id',
        'idempotency_key',
        'caf_folio_id',
    ];

    protected $casts = [
        'tipo_documento' => 'integer',
        'folio' => 'integer',
        'fecha_emision' => 'date',
        'monto_neto' => 'integer',
        'monto_iva' => 'integer',
        'tasa_iva' => 'decimal:2',
        'monto_exento' => 'integer',
        'monto_total' => 'integer',
        'envio_intentos' => 'integer',
        'ultimo_envio_at' => 'datetime',
    ];

    public function concession()
    {
        return $this->belongsTo(Concession::class, 'id_concession');
    }

    public function ordenServicio()
    {
        return $this->belongsTo(OrdenServicio::class, 'orden_servicio_id');
    }

    public function cafFolio()
    {
        return $this->belongsTo(CafFolio::class, 'caf_folio_id');
    }

    public function detalles()
    {
        return $this->hasMany(DocumentoTributarioDetalle::class, 'documento_id');
    }

    public function envios()
    {
        return $this->hasMany(EnvioSii::class, 'documento_id');
    }

    public function audits()
    {
        return $this->hasMany(AuditDte::class, 'documento_id');
    }

    public function getNombreTipoAttribute(): string
    {
        return CafFolio::nombreTipoDocumento($this->tipo_documento);
    }

    public function getEstadoBadgeAttribute(): string
    {
        $badges = [
            'borrador' => 'secondary',
            'emitido' => 'info',
            'enviado' => 'warning',
            'aceptado' => 'success',
            'rechazado' => 'danger',
            'anulado' => 'dark',
        ];
        return $badges[$this->estado] ?? 'secondary';
    }
}
