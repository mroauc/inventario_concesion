<?php

namespace App\DTOs;

class BoletaData
{
    public int $tipoDocumento;
    public int $folio;
    public string $fechaEmision;
    public ?int $indServicio;
    public EmisorData $emisor;
    public ReceptorData $receptor;
    /** @var DetalleItemData[] */
    public array $detalles;
    public int $montoNeto;
    public int $montoIva;
    public int $montoExento;
    public int $montoTotal;

    public function __construct(array $data)
    {
        $this->tipoDocumento = $data['tipo_documento'];
        $this->folio = $data['folio'];
        $this->fechaEmision = $data['fecha_emision'] ?? date('Y-m-d');
        $this->indServicio = $data['ind_servicio'] ?? 3;
        $this->emisor = $data['emisor'];
        $this->receptor = $data['receptor'];
        $this->detalles = $data['detalles'];
        $this->montoNeto = $data['monto_neto'];
        $this->montoIva = $data['monto_iva'];
        $this->montoExento = $data['monto_exento'] ?? 0;
        $this->montoTotal = $data['monto_total'];
    }

    public function getDocumentoId(): string
    {
        return "DTE_T{$this->tipoDocumento}F{$this->folio}";
    }
}
