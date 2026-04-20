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
            'Cocina',
            'Refrigeradores',
            'Lavadora',
            'Aspiradoras',
            'Empotrados',
            'Microondas',
            'Climatización',
            'Menaje',
            'Lavavajillas',
            'Campanas',
            'Encimera',
            'Hornos',
            'Secadora',
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
