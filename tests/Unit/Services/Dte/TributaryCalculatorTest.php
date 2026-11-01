<?php

namespace Tests\Unit\Services\Dte;

use App\DTOs\DetalleItemData;
use App\Services\Dte\Tax\TributaryCalculator;
use PHPUnit\Framework\TestCase;

class TributaryCalculatorTest extends TestCase
{
    private TributaryCalculator $calculator;

    protected function setUp(): void
    {
        parent::setUp();
        $this->calculator = new TributaryCalculator();
    }

    public function test_tasa_iva_es_19()
    {
        $this->assertEquals(19, $this->calculator->getTasaIva());
    }

    public function test_calcular_desde_bruto_basico()
    {
        $resultado = $this->calculator->calcularDesdeBruto(11900);

        $this->assertEquals(10000, $resultado['neto']);
        $this->assertEquals(1900, $resultado['iva']);
        $this->assertEquals(0, $resultado['exento']);
        $this->assertEquals(11900, $resultado['total']);
    }

    public function test_calcular_desde_bruto_redondeo()
    {
        $resultado = $this->calculator->calcularDesdeBruto(1000);

        $this->assertEquals(840, $resultado['neto']);
        $this->assertEquals(160, $resultado['iva']);
        $this->assertEquals(1000, $resultado['total']);
        $this->assertEquals($resultado['neto'] + $resultado['iva'], $resultado['total']);
    }

    public function test_calcular_desde_bruto_monto_minimo()
    {
        $resultado = $this->calculator->calcularDesdeBruto(1);

        $this->assertEquals(1, $resultado['total']);
        $this->assertEquals($resultado['neto'] + $resultado['iva'], $resultado['total']);
    }

    public function test_calcular_desde_neto()
    {
        $resultado = $this->calculator->calcularDesdeNeto(10000);

        $this->assertEquals(10000, $resultado['neto']);
        $this->assertEquals(1900, $resultado['iva']);
        $this->assertEquals(11900, $resultado['total']);
    }

    public function test_calcular_desde_neto_con_redondeo()
    {
        $resultado = $this->calculator->calcularDesdeNeto(333);

        $this->assertEquals(333, $resultado['neto']);
        $this->assertEquals(63, $resultado['iva']); // 333 * 0.19 = 63.27 => 63
        $this->assertEquals(396, $resultado['total']);
    }

    public function test_calcular_monto_item_simple()
    {
        $monto = $this->calculator->calcularMontoItem(5000, 2, 0);
        $this->assertEquals(10000, $monto);
    }

    public function test_calcular_monto_item_con_descuento()
    {
        $monto = $this->calculator->calcularMontoItem(5000, 2, 500);
        $this->assertEquals(9500, $monto);
    }

    public function test_calcular_monto_item_cantidad_fraccionaria()
    {
        $monto = $this->calculator->calcularMontoItem(1000, 1.5, 0);
        $this->assertEquals(1500, $monto);
    }

    public function test_calcular_detalles_boleta_un_item()
    {
        $detalles = [
            new DetalleItemData([
                'nro_linea' => 1,
                'nombre_item' => 'Filtro de aceite',
                'cantidad' => 1,
                'precio_unitario' => 11900,
                'monto_item' => 11900,
            ]),
        ];

        $resultado = $this->calculator->calcularDetallesBoleta($detalles);

        $this->assertEquals(10000, $resultado['neto']);
        $this->assertEquals(1900, $resultado['iva']);
        $this->assertEquals(11900, $resultado['total']);
    }

    public function test_calcular_detalles_boleta_multiples_items()
    {
        $detalles = [
            new DetalleItemData([
                'nro_linea' => 1,
                'nombre_item' => 'Filtro de aceite',
                'cantidad' => 2,
                'precio_unitario' => 5000,
                'monto_item' => 10000,
            ]),
            new DetalleItemData([
                'nro_linea' => 2,
                'nombre_item' => 'Aceite motor 5W30',
                'cantidad' => 1,
                'precio_unitario' => 15000,
                'monto_item' => 15000,
            ]),
        ];

        $resultado = $this->calculator->calcularDetallesBoleta($detalles);

        $this->assertEquals(25000, $resultado['total']);
        $this->assertEquals($resultado['neto'] + $resultado['iva'], $resultado['total']);
    }

    public function test_neto_mas_iva_siempre_igual_total()
    {
        $montos = [100, 999, 1234, 5678, 10000, 50000, 99999, 150000];

        foreach ($montos as $bruto) {
            $r = $this->calculator->calcularDesdeBruto($bruto);
            $this->assertEquals(
                $r['total'],
                $r['neto'] + $r['iva'],
                "Neto({$r['neto']}) + IVA({$r['iva']}) != Total({$r['total']}) para bruto={$bruto}"
            );
        }
    }
}
