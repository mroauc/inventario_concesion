<?php

namespace App\DTOs;

class ReceptorData
{
    public string $rutRecep;
    public string $razonSocialRecep;
    public ?string $direccion;
    public ?string $comuna;
    public ?string $ciudad;

    public function __construct(array $data)
    {
        $this->rutRecep = $data['rut'] ?? '66666666-6';
        $this->razonSocialRecep = mb_substr($data['razon_social'] ?? 'Consumidor Final', 0, 100);
        $this->direccion = $data['direccion'] ?? null;
        $this->comuna = $data['comuna'] ?? null;
        $this->ciudad = $data['ciudad'] ?? null;
    }

    public static function anonimo(): self
    {
        return new self([
            'rut' => '66666666-6',
            'razon_social' => 'Consumidor Final',
        ]);
    }
}
