<?php

namespace App\Models\Dte;

use App\Models\Concession;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class AuditDte extends Model
{
    protected $table = 'audit_dte';

    public $timestamps = false;

    protected $fillable = [
        'id_concession',
        'documento_id',
        'accion',
        'detalle',
        'estado_anterior',
        'estado_nuevo',
        'user_id',
        'ip_address',
        'created_at',
    ];

    protected $casts = [
        'detalle' => 'array',
        'created_at' => 'datetime',
    ];

    public function concession()
    {
        return $this->belongsTo(Concession::class, 'id_concession');
    }

    public function documento()
    {
        return $this->belongsTo(DocumentoTributario::class, 'documento_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public static function registrar(
        int $concessionId,
        ?int $documentoId,
        string $accion,
        ?array $detalle = null,
        ?string $estadoAnterior = null,
        ?string $estadoNuevo = null
    ): self {
        return static::create([
            'id_concession' => $concessionId,
            'documento_id' => $documentoId,
            'accion' => $accion,
            'detalle' => $detalle,
            'estado_anterior' => $estadoAnterior,
            'estado_nuevo' => $estadoNuevo,
            'user_id' => auth()->id(),
            'ip_address' => request()->ip(),
            'created_at' => now(),
        ]);
    }
}
