<?php

namespace App\DTOs;

class DetalleItemData
{
    public int $nroLinea;
    public string $nombreItem;
    public ?string $descripcion;
    public float $cantidad;
    public ?string $unidadMedida;
    public int $precioUnitario;
    public int $descuentoMonto;
    public int $montoItem;
    public ?int $productoId;
    public ?int $servicioId;

    public function __construct(array $data)
    {
        $this->nroLinea = $data['nro_linea'];
        $this->nombreItem = mb_substr($data['nombre_item'], 0, 80);
        $this->descripcion = $data['descripcion'] ?? null;
        $this->cantidad = $data['cantidad'] ?? 1;
        $this->unidadMedida = $data['unidad_medida'] ?? null;
        $this->precioUnitario = (int) $data['precio_unitario'];
        $this->descuentoMonto = (int) ($data['descuento_monto'] ?? 0);
        $this->montoItem = (int) $data['monto_item'];
        $this->productoId = $data['producto_id'] ?? null;
        $this->servicioId = $data['servicio_id'] ?? null;
    }
}
