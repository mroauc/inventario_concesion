<?php

namespace App\Console\Commands;

use App\Services\Dte\Sync\DteStatusSynchronizer;
use Illuminate\Console\Command;

class SyncDteStatusCommand extends Command
{
    protected $signature = 'dte:sync-status {--concession= : ID de concesion especifica}';

    protected $description = 'Sincronizar estado de documentos enviados al SII';

    public function handle(DteStatusSynchronizer $synchronizer): int
    {
        $concessionId = $this->option('concession') ? (int) $this->option('concession') : null;

        $this->info('Sincronizando estados de documentos DTE...');

        $resultados = $synchronizer->sincronizar($concessionId);

        $this->info("Total pendientes: {$resultados['total']}");
        $this->info("Actualizados: {$resultados['actualizados']}");

        if ($resultados['errores'] > 0) {
            $this->warn("Errores: {$resultados['errores']}");
        }

        return $resultados['errores'] > 0 ? 1 : 0;
    }
}
