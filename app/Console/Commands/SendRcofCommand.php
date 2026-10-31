<?php

namespace App\Console\Commands;

use App\Services\Dte\Sii\RcofSender;
use Illuminate\Console\Command;

class SendRcofCommand extends Command
{
    protected $signature = 'dte:enviar-rcof
                            {--fecha= : Fecha del RCOF (YYYY-MM-DD, default: ayer)}
                            {--concession= : ID de concesion especifica}';

    protected $description = 'Genera y envia el RCOF (Reporte de Consumo de Folios) al SII';

    public function handle(RcofSender $rcofSender): int
    {
        $fecha = $this->option('fecha') ?? now()->subDay()->format('Y-m-d');
        $concessionId = $this->option('concession');

        $this->info("Enviando RCOF para fecha: {$fecha}");

        if ($concessionId) {
            $config = \App\Models\Dte\ConfiguracionFiscal::where('id_concession', $concessionId)
                ->activa()
                ->first();

            if (!$config) {
                $this->error("No hay configuracion fiscal activa para concesion {$concessionId}");
                return 1;
            }

            $certificado = \App\Models\Dte\Certificado::where('id_concession', $concessionId)
                ->activo()
                ->vigente()
                ->first();

            if (!$certificado) {
                $this->error("No hay certificado vigente para concesion {$concessionId}");
                return 1;
            }

            try {
                $resultado = $rcofSender->enviar($config, $certificado, $fecha);

                if ($resultado['exitoso']) {
                    $this->info("RCOF enviado exitosamente. Track ID: {$resultado['track_id']}");
                } else {
                    $this->warn("RCOF enviado pero rechazado: {$resultado['glosa']}");
                }
            } catch (\Exception $e) {
                $this->error("Error: {$e->getMessage()}");
                return 1;
            }

            return 0;
        }

        $resultados = $rcofSender->enviarTodasLasConcesiones($fecha);

        $exitosos = collect($resultados)->where('exitoso', true)->count();
        $fallidos = collect($resultados)->where('exitoso', false)->count();

        $this->table(
            ['Concesion', 'Resultado', 'Track ID', 'Detalle'],
            collect($resultados)->map(fn ($r) => [
                $r['concession'],
                $r['exitoso'] ? 'OK' : 'ERROR',
                $r['track_id'] ?? '-',
                $r['glosa'] ?? '',
            ])->toArray()
        );

        $this->info("Completado: {$exitosos} exitosos, {$fallidos} fallidos.");

        return $fallidos > 0 ? 1 : 0;
    }
}
