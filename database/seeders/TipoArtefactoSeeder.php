<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\TipoArtefacto;

class TipoArtefactoSeeder extends Seeder
{
    /**
     * Carga los tipos de artefactos predeterminados para una concesión.
     *
     * @param int $id_concession
     * @return void
     */
    public function run($id_concession = null)
    {
        if (is_null($id_concession)) {
            $this->command->error('Se requiere el parámetro id_concession.');
            return;
        }

        $tipos = [
            'Aire Acondicionado',
            'Aspiradoras',
            'Batidoras',
            'Calefones',
            'Calentador de Agua',
            'Campanas',
            'Centrífugas',
            'Cocinas',
            'Enceradoras',
            'Estufas',
            'Freidoras',
            'Hidrolavadora',
            'Horno Eléctrico',
            'Lavado',
            'Lavavajillas',
            'Licua-Extrae-Exprime',
            'Microondas',
            'Planchas',
            'Purificador Agua',
            'Refrigeración',
            'Secarropa',
            'Termoacumulador',
            'Ventiladores',
        ];

        foreach ($tipos as $nombre) {
            TipoArtefacto::create([
                'nombre'       => $nombre,
                'descripcion'  => null,
                'id_concession' => $id_concession,
            ]);
        }
    }
}
