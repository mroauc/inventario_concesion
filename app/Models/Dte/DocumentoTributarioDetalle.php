<?php

namespace App\Models\Dte;

use App\Models\Product;
use App\Models\Servicio;
use Illuminate\Database\Eloquent\Model;

class DocumentoTributarioDetalle extends Model
{
    protected $table = 'documento_tributario_detalles';

    protected $fillable = [
        'documento_id',
        'nro_linea',
        'nombre_item',
        'descripcion',
        'cantidad',
        'unidad_medida',
        'precio_unitario',
        'descuento_pct',
        'descuento_monto',
        'monto_item',
        'producto_id',
        'servicio_id',
    ];

    protected $casts = [
        'nro_linea' => 'integer',
        'cantidad' => 'decimal:6',
        'precio_unitario' => 'decimal:2',
        'descuento_pct' => 'decimal:2',
        'descuento_monto' => 'integer',
        'monto_item' => 'integer',
    ];

    public function documento()
    {
        return $this->belongsTo(DocumentoTributario::class, 'documento_id');
    }

    public function producto()
    {
        return $this->belongsTo(Product::class, 'producto_id');
    }

    public function servicio()
    {
        return $this->belongsTo(Servicio::class, 'servicio_id');
    }
}
