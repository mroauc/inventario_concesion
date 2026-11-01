<?php

namespace Tests\Unit\Services\Dte;

use App\DTOs\BoletaData;
use App\DTOs\CaratulaData;
use App\DTOs\DetalleItemData;
use App\DTOs\EmisorData;
use App\DTOs\ReceptorData;
use App\Services\Dte\Xml\DteXmlBuilder;
use PHPUnit\Framework\TestCase;

class DteXmlBuilderTest extends TestCase
{
    private DteXmlBuilder $builder;

    protected function setUp(): void
    {
        parent::setUp();
        $this->builder = new DteXmlBuilder();
    }

    private function crearEmisor(): EmisorData
    {
        $e = new EmisorData();
        $e->rutEmisor = '76000000-0';
        $e->razonSocial = 'Empresa Test SpA';
        $e->giro = 'Venta de repuestos';
        $e->actividadEconomica = 503000;
        $e->direccion = 'Av Siempreviva 742';
        $e->comuna = 'Santiago';
        $e->ciudad = 'Santiago';
        $e->telefono = null;
        $e->emailDte = null;
        $e->sucursalSii = 0;
        $e->fechaResolucion = '2024-01-01';
        $e->numeroResolucion = 0;
        $e->ambiente = 'certificacion';
        return $e;
    }

    private function crearBoleta(array $overrides = []): BoletaData
    {
        $detalles = $overrides['detalles'] ?? [
            new DetalleItemData([
                'nro_linea' => 1,
                'nombre_item' => 'Filtro de aceite',
                'cantidad' => 2,
                'precio_unitario' => 5000,
                'monto_item' => 10000,
            ]),
        ];

        return new BoletaData(array_merge([
            'tipo_documento' => 39,
            'folio' => 123,
            'fecha_emision' => '2026-11-01',
            'ind_servicio' => 3,
            'emisor' => $this->crearEmisor(),
            'receptor' => ReceptorData::anonimo(),
            'detalles' => $detalles,
            'monto_neto' => 8403,
            'monto_iva' => 1597,
            'monto_exento' => 0,
            'monto_total' => 10000,
        ], $overrides));
    }

    public function test_construir_boleta_39_genera_xml_valido()
    {
        $boleta = $this->crearBoleta();
        $doc = $this->builder->construirBoleta39($boleta);

        $xml = $doc->saveXML();
        $this->assertNotEmpty($xml);

        $reloaded = new \DOMDocument();
        $this->assertTrue($reloaded->loadXML($xml));
    }

    public function test_boleta_39_tiene_namespace_sii()
    {
        $boleta = $this->crearBoleta();
        $doc = $this->builder->construirBoleta39($boleta);

        $this->assertEquals('http://www.sii.cl/SiiDte', $doc->documentElement->namespaceURI);
        $this->assertEquals('DTE', $doc->documentElement->localName);
    }

    public function test_boleta_39_tiene_documento_con_id_correcto()
    {
        $boleta = $this->crearBoleta(['folio' => 456]);
        $doc = $this->builder->construirBoleta39($boleta);

        $xpath = new \DOMXPath($doc);
        $xpath->registerNamespace('sii', 'http://www.sii.cl/SiiDte');
        $documentos = $xpath->query('//sii:Documento');

        $this->assertEquals(1, $documentos->length);
        $this->assertEquals('DTE_T39F456', $documentos->item(0)->getAttribute('ID'));
    }

    public function test_boleta_39_encabezado_tiene_tipo_folio_fecha()
    {
        $boleta = $this->crearBoleta();
        $doc = $this->builder->construirBoleta39($boleta);

        $xml = $doc->saveXML();
        $this->assertStringContainsString('<TipoDTE>39</TipoDTE>', $xml);
        $this->assertStringContainsString('<Folio>123</Folio>', $xml);
        $this->assertStringContainsString('<FchEmis>2026-11-01</FchEmis>', $xml);
    }

    public function test_boleta_39_tiene_emisor_completo()
    {
        $boleta = $this->crearBoleta();
        $doc = $this->builder->construirBoleta39($boleta);
        $xml = $doc->saveXML();

        $this->assertStringContainsString('<RUTEmisor>76000000-0</RUTEmisor>', $xml);
        $this->assertStringContainsString('<GiroEmisor>', $xml);
        $this->assertStringContainsString('<DirOrigen>', $xml);
        $this->assertStringContainsString('<CmnaOrigen>', $xml);
    }

    public function test_boleta_39_receptor_anonimo()
    {
        $boleta = $this->crearBoleta();
        $doc = $this->builder->construirBoleta39($boleta);
        $xml = $doc->saveXML();

        $this->assertStringContainsString('<RUTRecep>66666666-6</RUTRecep>', $xml);
    }

    public function test_boleta_39_receptor_con_datos()
    {
        $receptor = new ReceptorData([
            'rut' => '12345678-9',
            'razon_social' => 'Juan Perez',
            'direccion' => 'Calle 123',
            'comuna' => 'Providencia',
            'ciudad' => 'Santiago',
        ]);

        $boleta = $this->crearBoleta(['receptor' => $receptor]);
        $doc = $this->builder->construirBoleta39($boleta);
        $xml = $doc->saveXML();

        $this->assertStringContainsString('<RUTRecep>12345678-9</RUTRecep>', $xml);
        $this->assertStringContainsString('<DirRecep>', $xml);
        $this->assertStringContainsString('<CmnaRecep>', $xml);
    }

    public function test_boleta_39_totales()
    {
        $boleta = $this->crearBoleta();
        $doc = $this->builder->construirBoleta39($boleta);
        $xml = $doc->saveXML();

        $this->assertStringContainsString('<MntNeto>8403</MntNeto>', $xml);
        $this->assertStringContainsString('<IVA>1597</IVA>', $xml);
        $this->assertStringContainsString('<MntTotal>10000</MntTotal>', $xml);
    }

    public function test_boleta_39_totales_sin_exento_omite_tag()
    {
        $boleta = $this->crearBoleta(['monto_exento' => 0]);
        $doc = $this->builder->construirBoleta39($boleta);
        $xml = $doc->saveXML();

        $this->assertStringNotContainsString('<MntExe>', $xml);
    }

    public function test_boleta_39_detalles()
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
                'nombre_item' => 'Aceite 5W30',
                'cantidad' => 1,
                'precio_unitario' => 15000,
                'descuento_monto' => 1000,
                'monto_item' => 14000,
            ]),
        ];

        $boleta = $this->crearBoleta(['detalles' => $detalles, 'monto_total' => 24000]);
        $doc = $this->builder->construirBoleta39($boleta);
        $xml = $doc->saveXML();

        $this->assertStringContainsString('<NroLinDet>1</NroLinDet>', $xml);
        $this->assertStringContainsString('<NroLinDet>2</NroLinDet>', $xml);
        $this->assertStringContainsString('<QtyItem>2</QtyItem>', $xml);
        $this->assertStringContainsString('<PrcItem>5000</PrcItem>', $xml);
        $this->assertStringContainsString('<MontoItem>10000</MontoItem>', $xml);
        $this->assertStringContainsString('<DescuentoMonto>1000</DescuentoMonto>', $xml);
    }

    public function test_boleta_39_detalle_sin_descuento_omite_tag()
    {
        $boleta = $this->crearBoleta();
        $doc = $this->builder->construirBoleta39($boleta);
        $xml = $doc->saveXML();

        $this->assertStringNotContainsString('<DescuentoMonto>', $xml);
    }

    public function test_construir_envio_boleta()
    {
        $boleta = $this->crearBoleta();
        $dteDoc = $this->builder->construirBoleta39($boleta);

        $caratula = new CaratulaData([
            'rut_emisor' => '76000000-0',
            'rut_envia' => '11111111-1',
            'fecha_resolucion' => '2024-01-01',
            'numero_resolucion' => 0,
            'sub_totales' => [39 => 1],
        ]);

        $envioDoc = $this->builder->construirEnvioBoleta($dteDoc, $caratula);
        $xml = $envioDoc->saveXML();

        $this->assertStringContainsString('EnvioBOLETA', $xml);
        $this->assertStringContainsString('SetDTE', $xml);
        $this->assertStringContainsString('<RutEmisor>76000000-0</RutEmisor>', $xml);
        $this->assertStringContainsString('<RutEnvia>11111111-1</RutEnvia>', $xml);
        $this->assertStringContainsString('<RutReceptor>60803000-K</RutReceptor>', $xml);
        $this->assertStringContainsString('<TpoDTE>39</TpoDTE>', $xml);
        $this->assertStringContainsString('<NroDTE>1</NroDTE>', $xml);
    }

    public function test_envio_boleta_contiene_dte_importado()
    {
        $boleta = $this->crearBoleta(['folio' => 789]);
        $dteDoc = $this->builder->construirBoleta39($boleta);

        $caratula = new CaratulaData([
            'rut_emisor' => '76000000-0',
            'rut_envia' => '11111111-1',
            'fecha_resolucion' => '2024-01-01',
            'numero_resolucion' => 0,
            'sub_totales' => [39 => 1],
        ]);

        $envioDoc = $this->builder->construirEnvioBoleta($dteDoc, $caratula);
        $xml = $envioDoc->saveXML();

        $this->assertStringContainsString('<Folio>789</Folio>', $xml);
        $this->assertStringContainsString('DTE_T39F789', $xml);
    }
}
