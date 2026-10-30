<?php

namespace App\Services\Dte\Pdf;

use App\Models\Dte\DocumentoTributario;
use App\Models\Dte\ConfiguracionFiscal;
use Barryvdh\DomPDF\Facade\Pdf;

class DtePdfGenerator
{
    public function generarBoleta(DocumentoTributario $documento): \Barryvdh\DomPDF\PDF
    {
        $documento->load(['detalles', 'concession']);

        $config = ConfiguracionFiscal::where('id_concession', $documento->id_concession)->first();

        $tedBase64 = base64_encode($documento->ted_xml ?? '');

        $data = [
            'documento' => $documento,
            'config' => $config,
            'tedBase64' => $tedBase64,
        ];

        return Pdf::setOption('isFontSubsettingEnabled', true)
            ->loadView('dte.boletas.pdf', $data)
            ->setPaper([0, 0, 226.77, 600], 'portrait');
    }
}
