<?php

namespace App\DTOs;

class EmisorData
{
    public string $rutEmisor;
    public string $razonSocial;
    public string $giro;
    public int $actividadEconomica;
    public string $direccion;
    public string $comuna;
    public string $ciudad;
    public ?string $telefono;
    public ?string $emailDte;
    public int $sucursalSii;
    public string $fechaResolucion;
    public int $numeroResolucion;
    public string $ambiente;

    public static function fromConfiguracion(\App\Models\Dte\ConfiguracionFiscal $config): self
    {
        $dto = new self();
        $dto->rutEmisor = $config->rut_empresa;
        $dto->razonSocial = $config->razon_social;
        $dto->giro = mb_substr($config->giro, 0, 40);
        $dto->actividadEconomica = $config->actividad_economica;
        $dto->direccion = $config->direccion;
        $dto->comuna = $config->comuna;
        $dto->ciudad = $config->ciudad;
        $dto->telefono = $config->telefono;
        $dto->emailDte = $config->email_dte;
        $dto->sucursalSii = $config->sucursal_sii;
        $dto->fechaResolucion = $config->fecha_resolucion->format('Y-m-d');
        $dto->numeroResolucion = $config->numero_resolucion;
        $dto->ambiente = $config->ambiente;
        return $dto;
    }
}
