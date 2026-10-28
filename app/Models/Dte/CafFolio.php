<?php

namespace App\Models\Dte;

use App\Models\Concession;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;

class CafFolio extends Model
{
    protected $table = 'caf_folios';

    protected $fillable = [
        'id_concession',
        'tipo_documento',
        'folio_desde',
        'folio_hasta',
        'folio_actual',
        'xml_cifrado',
        'fecha_autorizacion',
        'fecha_vencimiento',
        'idk',
        'agotado',
    ];

    protected $casts = [
        'tipo_documento' => 'integer',
        'folio_desde' => 'integer',
        'folio_hasta' => 'integer',
        'folio_actual' => 'integer',
        'fecha_autorizacion' => 'date',
        'fecha_vencimiento' => 'date',
        'idk' => 'integer',
        'agotado' => 'boolean',
    ];

    protected $hidden = [
        'xml_cifrado',
    ];

    public function concession()
    {
        return $this->belongsTo(Concession::class, 'id_concession');
    }

    public function documentosTributarios()
    {
        return $this->hasMany(DocumentoTributario::class, 'caf_folio_id');
    }

    public function setXmlCifradoAttribute($value)
    {
        $this->attributes['xml_cifrado'] = Crypt::encryptString($value);
    }

    public function getXmlDescifradoAttribute(): string
    {
        return Crypt::decryptString($this->attributes['xml_cifrado']);
    }

    public function scopeDisponible($query)
    {
        return $query->where('agotado', false);
    }

    public function isVigente(): bool
    {
        if ($this->fecha_vencimiento === null) {
            return true;
        }
        return $this->fecha_vencimiento->isFuture();
    }

    public function getFoliosRestantesAttribute(): int
    {
        return max(0, $this->folio_hasta - $this->folio_actual + 1);
    }

    public function getFoliosUsadosAttribute(): int
    {
        return $this->folio_actual - $this->folio_desde;
    }

    public function getFoliosTotalAttribute(): int
    {
        return $this->folio_hasta - $this->folio_desde + 1;
    }

    public function getPorcentajeUsoAttribute(): float
    {
        $total = $this->folios_total;
        if ($total === 0) return 100;
        return round(($this->folios_usados / $total) * 100, 1);
    }

    public static function nombreTipoDocumento(int $tipo): string
    {
        $tipos = [
            39 => 'Boleta Electr. Afecta',
            41 => 'Boleta Electr. Exenta',
            33 => 'Factura Electr.',
            34 => 'Factura Electr. Exenta',
            61 => 'Nota de Crédito Electr.',
            56 => 'Nota de Débito Electr.',
            52 => 'Guía de Despacho Electr.',
        ];
        return $tipos[$tipo] ?? "Tipo $tipo";
    }
}
