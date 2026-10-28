<?php

namespace App\DTOs;

class CaratulaData
{
    public string $rutEmisor;
    public string $rutEnvia;
    public string $rutReceptor;
    public string $fechaResolucion;
    public int $numeroResolucion;
    public string $timestampFirma;
    /** @var array<int, int> tipo_documento => cantidad */
    public array $subTotales;

    public function __construct(array $data)
    {
        $this->rutEmisor = $data['rut_emisor'];
        $this->rutEnvia = $data['rut_envia'];
        $this->rutReceptor = $data['rut_receptor'] ?? '60803000-K';
        $this->fechaResolucion = $data['fecha_resolucion'];
        $this->numeroResolucion = $data['numero_resolucion'];
        $this->timestampFirma = $data['timestamp_firma'] ?? date('Y-m-d\TH:i:s');
        $this->subTotales = $data['sub_totales'];
    }
}
