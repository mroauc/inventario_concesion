<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        // \App\Models\User::factory(10)->create();

        // Ejemplo: cargar tipos de artefactos para una concesión existente
        // $this->call(TipoArtefactoSeeder::class, false, ['id_concession' => 1]);
    }
}
