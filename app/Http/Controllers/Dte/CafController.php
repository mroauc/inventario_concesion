<?php

namespace App\Http\Controllers\Dte;

use App\Http\Controllers\Controller;
use App\Models\Dte\CafFolio;
use App\Models\Dte\ConfiguracionFiscal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Laracasts\Flash\Flash;

class CafController extends Controller
{
    public function index()
    {
        $concessionId = auth()->user()->id_concession;
        $cafs = CafFolio::where('id_concession', $concessionId)
            ->orderBy('tipo_documento')
            ->orderByDesc('folio_desde')
            ->get();

        return view('dte.cafs.index', compact('cafs'));
    }

    public function create()
    {
        return view('dte.cafs.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'archivo_caf' => 'required|file|max:1024',
        ]);

        $concessionId = auth()->user()->id_concession;
        $config = ConfiguracionFiscal::where('id_concession', $concessionId)->first();

        if (!$config) {
            Flash::error('Debe configurar los datos fiscales antes de subir un CAF.');
            return redirect()->route('dte.configuracion.index');
        }

        $xmlRaw = file_get_contents($request->file('archivo_caf')->getRealPath());

        $doc = new \DOMDocument();
        if (!@$doc->loadXML($xmlRaw)) {
            Flash::error('El archivo no es un XML valido.');
            return back();
        }

        $xpath = new \DOMXPath($doc);

        $re = $this->xpathValue($xpath, '//AUTORIZACION/CAF/DA/RE');
        $td = $this->xpathValue($xpath, '//AUTORIZACION/CAF/DA/TD');
        $desde = $this->xpathValue($xpath, '//AUTORIZACION/CAF/DA/RNG/D');
        $hasta = $this->xpathValue($xpath, '//AUTORIZACION/CAF/DA/RNG/H');
        $fa = $this->xpathValue($xpath, '//AUTORIZACION/CAF/DA/FA');
        $idk = $this->xpathValue($xpath, '//AUTORIZACION/CAF/DA/IDK');
        $rsask = $this->xpathValue($xpath, '//AUTORIZACION/RSASK');

        if (!$re || !$td || !$desde || !$hasta || !$fa || !$idk) {
            Flash::error('El archivo CAF no tiene la estructura esperada.');
            return back();
        }

        if (!$rsask) {
            Flash::error('El CAF no contiene clave privada (RSASK). Verifique el archivo.');
            return back();
        }

        if (strtoupper($re) !== strtoupper($config->rut_empresa)) {
            Flash::error("El RUT del CAF ({$re}) no coincide con el RUT de la empresa ({$config->rut_empresa}).");
            return back();
        }

        $idkEsperado = $config->idk_esperado;
        if ((int) $idk !== $idkEsperado) {
            $ambienteDesc = $config->isProduccion() ? 'produccion (300)' : 'certificacion (100)';
            Flash::error("El IDK del CAF ({$idk}) no corresponde al ambiente {$ambienteDesc}.");
            return back();
        }

        $td = (int) $td;
        $desde = (int) $desde;
        $hasta = (int) $hasta;

        $solapado = CafFolio::where('id_concession', $concessionId)
            ->where('tipo_documento', $td)
            ->where(function ($q) use ($desde, $hasta) {
                $q->whereBetween('folio_desde', [$desde, $hasta])
                  ->orWhereBetween('folio_hasta', [$desde, $hasta])
                  ->orWhere(function ($q2) use ($desde, $hasta) {
                      $q2->where('folio_desde', '<=', $desde)
                          ->where('folio_hasta', '>=', $hasta);
                  });
            })
            ->exists();

        if ($solapado) {
            Flash::error("Ya existe un CAF con rango solapado para tipo {$td}.");
            return back();
        }

        $tiposConVencimiento = [33, 43, 46, 56, 61];
        $fechaVencimiento = in_array($td, $tiposConVencimiento)
            ? date('Y-m-d', strtotime($fa . ' +180 days'))
            : null;

        $caf = new CafFolio();
        $caf->id_concession = $concessionId;
        $caf->tipo_documento = $td;
        $caf->folio_desde = $desde;
        $caf->folio_hasta = $hasta;
        $caf->folio_actual = $desde;
        $caf->xml_cifrado = $xmlRaw;
        $caf->fecha_autorizacion = $fa;
        $caf->fecha_vencimiento = $fechaVencimiento;
        $caf->idk = (int) $idk;
        $caf->agotado = false;
        $caf->save();

        $tipoNombre = CafFolio::nombreTipoDocumento($td);
        Flash::success("CAF cargado: {$tipoNombre}, folios {$desde} a {$hasta}.");
        return redirect()->route('dte.cafs.index');
    }

    public function show(CafFolio $caf)
    {
        if ($caf->id_concession !== auth()->user()->id_concession) {
            abort(403);
        }

        return view('dte.cafs.show', compact('caf'));
    }

    private function xpathValue(\DOMXPath $xpath, string $expression): ?string
    {
        $nodes = $xpath->query($expression);
        if ($nodes && $nodes->length > 0) {
            return trim($nodes->item(0)->textContent);
        }
        return null;
    }
}
