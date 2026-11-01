<?php

namespace Tests\Unit\Services\Dte;

use App\DTOs\BoletaData;
use App\DTOs\DetalleItemData;
use App\DTOs\EmisorData;
use App\DTOs\ReceptorData;
use App\Exceptions\DteException;
use App\Services\Dte\Folio\CafManager;
use App\Services\Dte\Xml\DteXmlBuilder;
use App\Services\Dte\Xml\TedGenerator;
use PHPUnit\Framework\TestCase;

class TedGeneratorTest extends TestCase
{
    private TedGenerator $generator;
    private static ?\OpenSSLAsymmetricKey $testKey = null;

    protected function setUp(): void
    {
        parent::setUp();
        $this->generator = new TedGenerator();
    }

    private static function getTestKey(): \OpenSSLAsymmetricKey
    {
        if (self::$testKey === null) {
            self::$testKey = openssl_pkey_new([
                'private_key_bits' => 1024,
                'private_key_type' => OPENSSL_KEYTYPE_RSA,
            ]);
        }
        return self::$testKey;
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
            'folio' => 100,
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

    private function crearCafManagerMock(): CafManager
    {
        $cafXml = '<CAF version="1.0"><DA><RE>76000000-0</RE><RS>Empresa Test SpA</RS>'
            . '<TD>39</TD><RNG><D>1</D><H>200</H></RNG>'
            . '<FA>2024-01-01</FA><IDK>100</IDK></DA></CAF>';

        $mock = $this->createMock(CafManager::class);
        $mock->method('getCafXmlNodo')->willReturn($cafXml);
        $mock->method('getClavePrivada')->willReturn(self::getTestKey());

        return $mock;
    }

    public function test_generar_ted_retorna_xml_valido()
    {
        $boleta = $this->crearBoleta();
        $cafManager = $this->crearCafManagerMock();

        $tedXml = $this->generator->generar($boleta, $cafManager);

        $this->assertNotEmpty($tedXml);

        $doc = new \DOMDocument();
        $this->assertTrue($doc->loadXML($tedXml));
    }

    public function test_ted_contiene_dd_con_campos_requeridos()
    {
        $boleta = $this->crearBoleta();
        $cafManager = $this->crearCafManagerMock();

        $tedXml = $this->generator->generar($boleta, $cafManager);

        $this->assertStringContainsString('<RE>76000000-0</RE>', $tedXml);
        $this->assertStringContainsString('<TD>39</TD>', $tedXml);
        $this->assertStringContainsString('<F>100</F>', $tedXml);
        $this->assertStringContainsString('<FE>2026-11-01</FE>', $tedXml);
        $this->assertStringContainsString('<RR>66666666-6</RR>', $tedXml);
        $this->assertStringContainsString('<MNT>10000</MNT>', $tedXml);
        $this->assertStringContainsString('<IT1>Filtro de aceite</IT1>', $tedXml);
    }

    public function test_ted_contiene_firma_sha1withrsa()
    {
        $boleta = $this->crearBoleta();
        $cafManager = $this->crearCafManagerMock();

        $tedXml = $this->generator->generar($boleta, $cafManager);

        $this->assertStringContainsString('<FRMT', $tedXml);
        $this->assertStringContainsString('algoritmo="SHA1withRSA"', $tedXml);
    }

    public function test_ted_contiene_caf_importado()
    {
        $boleta = $this->crearBoleta();
        $cafManager = $this->crearCafManagerMock();

        $tedXml = $this->generator->generar($boleta, $cafManager);

        $this->assertStringContainsString('<CAF', $tedXml);
        $this->assertStringContainsString('<IDK>100</IDK>', $tedXml);
    }

    public function test_ted_contiene_timestamp()
    {
        $boleta = $this->crearBoleta();
        $cafManager = $this->crearCafManagerMock();

        $tedXml = $this->generator->generar($boleta, $cafManager);

        $this->assertStringContainsString('<TSTED>', $tedXml);
        $this->assertMatchesRegularExpression('/\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}/', $tedXml);
    }

    public function test_ted_trunca_nombre_item_a_40_chars()
    {
        $detalles = [
            new DetalleItemData([
                'nro_linea' => 1,
                'nombre_item' => str_repeat('A', 60),
                'cantidad' => 1,
                'precio_unitario' => 1000,
                'monto_item' => 1000,
            ]),
        ];

        $boleta = $this->crearBoleta(['detalles' => $detalles, 'monto_total' => 1000]);
        $cafManager = $this->crearCafManagerMock();

        $tedXml = $this->generator->generar($boleta, $cafManager);

        $this->assertStringContainsString('<IT1>' . str_repeat('A', 40) . '</IT1>', $tedXml);
        $this->assertStringNotContainsString(str_repeat('A', 41), $tedXml);
    }

    public function test_ted_trunca_razon_social_receptor_a_40_chars()
    {
        $receptor = new ReceptorData([
            'rut' => '12345678-9',
            'razon_social' => str_repeat('B', 60),
        ]);

        $boleta = $this->crearBoleta(['receptor' => $receptor]);
        $cafManager = $this->crearCafManagerMock();

        $tedXml = $this->generator->generar($boleta, $cafManager);

        $this->assertStringContainsString('<RSR>' . str_repeat('B', 40) . '</RSR>', $tedXml);
        $this->assertStringNotContainsString(str_repeat('B', 41), $tedXml);
    }

    public function test_firma_ted_es_verificable()
    {
        $boleta = $this->crearBoleta();
        $cafManager = $this->crearCafManagerMock();

        $tedXml = $this->generator->generar($boleta, $cafManager);

        $doc = new \DOMDocument();
        $doc->loadXML($tedXml);

        $xpath = new \DOMXPath($doc);
        $ddNodes = $xpath->query('//DD');
        $this->assertEquals(1, $ddNodes->length);

        $frmtNodes = $xpath->query('//FRMT');
        $this->assertEquals(1, $frmtNodes->length);

        $firma = base64_decode($frmtNodes->item(0)->textContent);
        $this->assertNotEmpty($firma);

        $ddC14n = $ddNodes->item(0)->C14N();
        $ddIso = mb_convert_encoding($ddC14n, 'ISO-8859-1', 'UTF-8');
        $ddFlat = preg_replace('/>\s+</', '><', $ddIso);

        $pubKey = openssl_pkey_get_details(self::getTestKey())['key'];
        $verified = openssl_verify($ddFlat, $firma, $pubKey, OPENSSL_ALGO_SHA1);
        $this->assertEquals(1, $verified, 'La firma del TED debe ser verificable con la clave publica');
    }

    public function test_insertar_en_documento()
    {
        $builder = new DteXmlBuilder();
        $boleta = $this->crearBoleta(['folio' => 555]);
        $dteDoc = $builder->construirBoleta39($boleta);

        $tedXml = '<TED version="1.0"><DD><RE>76000000-0</RE></DD></TED>';

        $this->generator->insertarEnDocumento($dteDoc, $tedXml, 'DTE_T39F555');

        $xml = $dteDoc->saveXML();
        $this->assertStringContainsString('<TED', $xml);
        $this->assertStringContainsString('<RE>76000000-0</RE>', $xml);
    }

    public function test_insertar_en_documento_id_inexistente_lanza_excepcion()
    {
        $builder = new DteXmlBuilder();
        $boleta = $this->crearBoleta();
        $dteDoc = $builder->construirBoleta39($boleta);

        $tedXml = '<TED version="1.0"><DD><RE>76000000-0</RE></DD></TED>';

        $this->expectException(DteException::class);
        $this->expectExceptionMessage('No se encontro Documento con ID=ID_INVALIDO');
        $this->generator->insertarEnDocumento($dteDoc, $tedXml, 'ID_INVALIDO');
    }
}
