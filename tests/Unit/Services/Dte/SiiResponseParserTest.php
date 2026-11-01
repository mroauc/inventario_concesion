<?php

namespace Tests\Unit\Services\Dte;

use App\Exceptions\DteException;
use App\Services\Dte\Sii\SiiResponseParser;
use PHPUnit\Framework\TestCase;

class SiiResponseParserTest extends TestCase
{
    private SiiResponseParser $parser;

    protected function setUp(): void
    {
        parent::setUp();
        $this->parser = new SiiResponseParser();
    }

    public function test_parsear_respuesta_envio_exitoso()
    {
        $xml = '<?xml version="1.0" encoding="UTF-8"?>
        <RECEPCIONDTE>
            <STATUS>0</STATUS>
            <TRACKID>123456</TRACKID>
            <GLOSA>Envio recibido correctamente</GLOSA>
        </RECEPCIONDTE>';

        $resultado = $this->parser->parsearRespuestaEnvio($xml);

        $this->assertTrue($resultado['exitoso']);
        $this->assertEquals(123456, $resultado['track_id']);
        $this->assertEquals(0, $resultado['status_code']);
        $this->assertStringContainsString('recibido', $resultado['glosa']);
    }

    public function test_parsear_respuesta_envio_rechazado()
    {
        $xml = '<?xml version="1.0" encoding="UTF-8"?>
        <RECEPCIONDTE>
            <STATUS>2</STATUS>
            <GLOSA>Error de schema</GLOSA>
        </RECEPCIONDTE>';

        $resultado = $this->parser->parsearRespuestaEnvio($xml);

        $this->assertFalse($resultado['exitoso']);
        $this->assertNull($resultado['track_id']);
        $this->assertEquals(2, $resultado['status_code']);
    }

    public function test_parsear_respuesta_envio_xml_vacio_lanza_excepcion()
    {
        $this->expectException(DteException::class);
        $this->parser->parsearRespuestaEnvio('');
    }

    public function test_parsear_respuesta_envio_xml_invalido_lanza_excepcion()
    {
        $this->expectException(DteException::class);
        $this->parser->parsearRespuestaEnvio('esto no es xml');
    }

    public function test_parsear_estado_envio_aceptado()
    {
        $xml = '<?xml version="1.0" encoding="UTF-8"?>
        <SII:RESP_HDR xmlns:SII="http://www.sii.cl/XMLSchema">
            <ESTADO>EPR</ESTADO>
            <GLOSA>Envio procesado</GLOSA>
            <NUM_ATENCION>12345</NUM_ATENCION>
        </SII:RESP_HDR>';

        $resultado = $this->parser->parsearEstadoEnvio($xml);

        $this->assertEquals('aceptado', $resultado['estado']);
        $this->assertEquals('EPR', $resultado['estado_raw']);
        $this->assertTrue($resultado['es_final']);
    }

    public function test_parsear_estado_envio_rechazado_rct()
    {
        $xml = '<?xml version="1.0" encoding="UTF-8"?>
        <SII:RESP_HDR xmlns:SII="http://www.sii.cl/XMLSchema">
            <ESTADO>RCT</ESTADO>
            <GLOSA>Rechazado por error en contenido</GLOSA>
        </SII:RESP_HDR>';

        $resultado = $this->parser->parsearEstadoEnvio($xml);

        $this->assertEquals('rechazado', $resultado['estado']);
        $this->assertTrue($resultado['es_final']);
    }

    public function test_parsear_estado_envio_en_proceso()
    {
        $xml = '<?xml version="1.0" encoding="UTF-8"?>
        <SII:RESP_HDR xmlns:SII="http://www.sii.cl/XMLSchema">
            <ESTADO>SOK</ESTADO>
            <GLOSA>Schema OK</GLOSA>
        </SII:RESP_HDR>';

        $resultado = $this->parser->parsearEstadoEnvio($xml);

        $this->assertEquals('enviado', $resultado['estado']);
        $this->assertFalse($resultado['es_final']);
    }

    public function test_parsear_estado_envio_todos_los_rechazos()
    {
        $rechazos = ['RSC', 'RCT', 'RFR', 'RPT', 'RPR', 'RCH'];

        foreach ($rechazos as $codigo) {
            $xml = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>
            <SII:RESP_HDR xmlns:SII=\"http://www.sii.cl/XMLSchema\">
                <ESTADO>{$codigo}</ESTADO>
                <GLOSA>Rechazado</GLOSA>
            </SII:RESP_HDR>";

            $resultado = $this->parser->parsearEstadoEnvio($xml);

            $this->assertEquals('rechazado', $resultado['estado'], "Codigo {$codigo} deberia ser rechazado");
            $this->assertTrue($resultado['es_final'], "Codigo {$codigo} deberia ser estado final");
        }
    }

    public function test_parsear_estado_documento()
    {
        $xml = '<?xml version="1.0" encoding="UTF-8"?>
        <SII:RESP_HDR xmlns:SII="http://www.sii.cl/XMLSchema">
            <ESTADO>DOK</ESTADO>
            <GLOSA>Documento OK</GLOSA>
        </SII:RESP_HDR>';

        $resultado = $this->parser->parsearEstadoDocumento($xml);

        $this->assertEquals('DOK', $resultado['estado_raw']);
        $this->assertStringContainsString('OK', $resultado['glosa']);
    }

    public function test_parsear_estado_documento_con_glosa_err()
    {
        $xml = '<?xml version="1.0" encoding="UTF-8"?>
        <SII:RESP_HDR xmlns:SII="http://www.sii.cl/XMLSchema">
            <ESTADO>DNK</ESTADO>
            <GLOSA>Algo</GLOSA>
            <GLOSA_ERR>RUT emisor no corresponde</GLOSA_ERR>
        </SII:RESP_HDR>';

        $resultado = $this->parser->parsearEstadoDocumento($xml);

        $this->assertEquals('RUT emisor no corresponde', $resultado['glosa']);
    }
}
