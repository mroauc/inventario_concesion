<?php

namespace App\Services\Dte\Folio;

use App\Exceptions\DteException;
use App\Exceptions\FoliosAgotadosException;
use App\Models\Dte\CafFolio;
use Illuminate\Support\Facades\DB;

class FolioAllocator
{
    public function asignar(int $concessionId, int $tipoDocumento): array
    {
        return DB::transaction(function () use ($concessionId, $tipoDocumento) {
            $caf = CafFolio::where('id_concession', $concessionId)
                ->where('tipo_documento', $tipoDocumento)
                ->where('agotado', false)
                ->where(function ($q) {
                    $q->whereNull('fecha_vencimiento')
                      ->orWhere('fecha_vencimiento', '>', now());
                })
                ->lockForUpdate()
                ->orderBy('folio_desde')
                ->first();

            if (!$caf) {
                throw new FoliosAgotadosException(
                    "Sin folios disponibles para tipo documento {$tipoDocumento}."
                );
            }

            $folio = $caf->folio_actual;

            if ($folio > $caf->folio_hasta) {
                $caf->update(['agotado' => true]);
                throw new FoliosAgotadosException(
                    "CAF agotado para tipo documento {$tipoDocumento}. Rango: {$caf->folio_desde}-{$caf->folio_hasta}."
                );
            }

            $caf->increment('folio_actual');

            if ($caf->folio_actual > $caf->folio_hasta) {
                $caf->update(['agotado' => true]);
            }

            return ['folio' => $folio, 'caf' => $caf->fresh()];
        });
    }
}
