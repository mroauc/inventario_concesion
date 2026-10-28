<?php

namespace App\Services\Dte\Tax;

use App\DTOs\DetalleItemData;

class TributaryCalculator
{
    private const TASA_IVA = 19;

    public function calcularDesdeBruto(int $montoTotal): array
    {
        $neto = (int) round($montoTotal / (1 + self::TASA_IVA / 100));
        $iva = $montoTotal - $neto;

        return ['neto' => $neto, 'iva' => $iva, 'exento' => 0, 'total' => $montoTotal];
    }

    public function calcularDesdeNeto(int $montoNeto): array
    {
        $iva = (int) round($montoNeto * self::TASA_IVA / 100);

        return ['neto' => $montoNeto, 'iva' => $iva, 'exento' => 0, 'total' => $montoNeto + $iva];
    }

    /**
     * @param DetalleItemData[] $detalles
     */
    public function calcularDetallesBoleta(array $detalles): array
    {
        $totalBruto = 0;
        foreach ($detalles as $detalle) {
            $totalBruto += $detalle->montoItem;
        }

        return $this->calcularDesdeBruto($totalBruto);
    }

    public function calcularMontoItem(int $precioUnitario, float $cantidad, int $descuento = 0): int
    {
        return (int) round($precioUnitario * $cantidad) - $descuento;
    }

    public function getTasaIva(): int
    {
        return self::TASA_IVA;
    }
}
