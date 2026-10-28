<?php

namespace App\Models\Dte;

use App\Models\Concession;
use Illuminate\Database\Eloquent\Model;

class EnvioSii extends Model
{
    protected $table = 'envios_sii';

    protected $fillable = [
        'id_concession',
        'tipo_envio',
        'documento_id',
        'xml_enviado_hash',
        'respuesta_raw',
        'track_id',
        'status_code',
        'http_status',
        'intentos',
        'ambiente',
    ];

    protected $casts = [
        'status_code' => 'integer',
        'http_status' => 'integer',
        'intentos' => 'integer',
    ];

    public function concession()
    {
        return $this->belongsTo(Concession::class, 'id_concession');
    }

    public function documento()
    {
        return $this->belongsTo(DocumentoTributario::class, 'documento_id');
    }

    public function isExitoso(): bool
    {
        return $this->status_code === 0;
    }
}
