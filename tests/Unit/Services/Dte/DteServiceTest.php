<?php

namespace Tests\Unit\Services\Dte;

use App\DTOs\DetalleItemData;
use App\DTOs\ReceptorData;
use App\Services\Dte\DteService;
use App\Services\Dte\Folio\CafManager;
use App\Services\Dte\Folio\FolioAllocator;
use App\Services\Dte\Sii\SiiAuthClient;
use App\Services\Dte\Sii\SiiBoletaClient;
use App\Services\Dte\Tax\TributaryCalculator;
use App\Services\Dte\Xml\DteXmlBuilder;
use App\Services\Dte\Xml\TedGenerator;
use App\Services\Dte\Xml\XmlSigner;
use PHPUnit\Framework\TestCase;
use ReflectionMethod;

class DteServiceTest extends TestCase
{
    private DteService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = new DteService(
            new TributaryCalculator(),
            $this->createMock(FolioAllocator::class),
            $this->createMock(CafManager::class),
            new DteXmlBuilder(),
            new TedGenerator(),
            $this->createMock(XmlSigner::class),
            $this->createMock(SiiAuthClient::class),
            $this->createMock(SiiBoletaClient::class)
        );
    }

    private function invocarMetodoPrivado(string $metodo, array $args)
    {
        $ref = new ReflectionMethod(DteService::class, $metodo);
        $ref->setAccessible(true);
        return $ref->invokeArgs($this->service, $args);
    }

    public function test_construir_detalles_un_item()
    {
        $items = [
            [
                'nombre' => 'Filtro de aceite',
                'precio_unitario' => 5000,
                'cantidad' => 2,
            ],
        ];

        $detalles = $this->invocarMetodoPrivado('construirDetalles', [$items]);

        $this->assertCount(1, $detalles);
        $this->assertInstanceOf(DetalleItemData::class, $detalles[0]);
        $this->assertEquals(1, $detalles[0]->nroLinea);
        $this->assertEquals('Filtro de aceite', $detalles[0]->nombreItem);
        $this->assertEquals(2, $detalles[0]->cantidad);
        $this->assertEquals(5000, $detalles[0]->precioUnitario);
        $this->assertEquals(10000, $detalles[0]->montoItem);
    }

    public function test_construir_detalles_multiples_items()
    {
        $items = [
            ['nombre' => 'Item A', 'precio_unitario' => 1000, 'cantidad' => 1],
            ['nombre' => 'Item B', 'precio_unitario' => 2000, 'cantidad' => 3],
            ['nombre' => 'Item C', 'precio_unitario' => 500, 'cantidad' => 5],
        ];

        $detalles = $this->invocarMetodoPrivado('construirDetalles', [$items]);

        $this->assertCount(3, $detalles);
        $this->assertEquals(1, $detalles[0]->nroLinea);
        $this->assertEquals(2, $detalles[1]->nroLinea);
        $this->assertEquals(3, $detalles[2]->nroLinea);
    }

    public function test_construir_detalles_con_descuento()
    {
        $items = [
            [
                'nombre' => 'Aceite 5W30',
                'precio_unitario' => 15000,
                'cantidad' => 1,
                'descuento' => 2000,
            ],
        ];

        $detalles = $this->invocarMetodoPrivado('construirDetalles', [$items]);

        $this->assertEquals(2000, $detalles[0]->descuentoMonto);
        $this->assertEquals(13000, $detalles[0]->montoItem);
    }

    public function test_construir_detalles_cantidad_por_defecto_es_uno()
    {
        $items = [
            ['nombre' => 'Servicio', 'precio_unitario' => 8000],
        ];

        $detalles = $this->invocarMetodoPrivado('construirDetalles', [$items]);

        $this->assertEquals(1, $detalles[0]->cantidad);
        $this->assertEquals(8000, $detalles[0]->montoItem);
    }

    public function test_construir_detalles_con_campos_opcionales()
    {
        $items = [
            [
                'nombre' => 'Pieza especial',
                'precio_unitario' => 3000,
                'cantidad' => 1,
                'descripcion' => 'Pieza importada premium',
                'unidad_medida' => 'UN',
                'producto_id' => 42,
                'servicio_id' => null,
            ],
        ];

        $detalles = $this->invocarMetodoPrivado('construirDetalles', [$items]);

        $this->assertEquals('Pieza importada premium', $detalles[0]->descripcion);
        $this->assertEquals('UN', $detalles[0]->unidadMedida);
        $this->assertEquals(42, $detalles[0]->productoId);
        $this->assertNull($detalles[0]->servicioId);
    }

    public function test_construir_receptor_desde_array_con_datos()
    {
        $datos = [
            'rut' => '12345678-9',
            'razon_social' => 'Juan Perez',
            'direccion' => 'Calle 123',
            'comuna' => 'Providencia',
            'ciudad' => 'Santiago',
        ];

        $receptor = $this->invocarMetodoPrivado('construirReceptorDesdeArray', [$datos]);

        $this->assertInstanceOf(ReceptorData::class, $receptor);
        $this->assertEquals('12345678-9', $receptor->rutRecep);
        $this->assertEquals('Juan Perez', $receptor->razonSocialRecep);
        $this->assertEquals('Calle 123', $receptor->direccion);
        $this->assertEquals('Providencia', $receptor->comuna);
    }

    public function test_construir_receptor_desde_array_null_retorna_anonimo()
    {
        $receptor = $this->invocarMetodoPrivado('construirReceptorDesdeArray', [null]);

        $this->assertEquals('66666666-6', $receptor->rutRecep);
        $this->assertEquals('Consumidor Final', $receptor->razonSocialRecep);
    }

    public function test_construir_receptor_desde_array_sin_rut_retorna_anonimo()
    {
        $datos = ['razon_social' => 'Test'];

        $receptor = $this->invocarMetodoPrivado('construirReceptorDesdeArray', [$datos]);

        $this->assertEquals('66666666-6', $receptor->rutRecep);
    }

    public function test_construir_receptor_desde_array_rut_vacio_retorna_anonimo()
    {
        $datos = ['rut' => '', 'razon_social' => 'Test'];

        $receptor = $this->invocarMetodoPrivado('construirReceptorDesdeArray', [$datos]);

        $this->assertEquals('66666666-6', $receptor->rutRecep);
    }

    public function test_construir_receptor_desde_array_sin_razon_social_usa_sin_nombre()
    {
        $datos = ['rut' => '11111111-1'];

        $receptor = $this->invocarMetodoPrivado('construirReceptorDesdeArray', [$datos]);

        $this->assertEquals('11111111-1', $receptor->rutRecep);
        $this->assertEquals('Sin Nombre', $receptor->razonSocialRecep);
    }
}
