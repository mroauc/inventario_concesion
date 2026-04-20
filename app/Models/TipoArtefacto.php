<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TipoArtefacto extends Model
{
    use HasFactory;

    protected $table = 'tipo_artefactos';

    protected $fillable = [
        'nombre',
        'descripcion',
        'id_concession',
    ];

    public function artefactos()
    {
        return $this->hasMany(Artefacto::class);
    }

    /**
     * Carga los tipos de artefactos predeterminados para una concesión.
     * Solo inserta los que aún no existan para esa concesión.
     *
     * @param int $id_concession
     * @return void
     */
    public static function cargarPredeterminados(int $id_concession): void
    {
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

        $existentes = self::where('id_concession', $id_concession)
            ->pluck('nombre')
            ->toArray();

        foreach ($tipos as $nombre) {
            if (!in_array($nombre, $existentes)) {
                self::create([
                    'nombre'        => $nombre,
                    'descripcion'   => null,
                    'id_concession' => $id_concession,
                ]);
            }
        }
    }
}
