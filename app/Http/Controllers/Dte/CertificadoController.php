<?php

namespace App\Http\Controllers\Dte;

use App\Http\Controllers\Controller;
use App\Models\Dte\Certificado;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Laracasts\Flash\Flash;

class CertificadoController extends Controller
{
    public function index()
    {
        $concessionId = auth()->user()->id_concession;
        $certificados = Certificado::where('id_concession', $concessionId)
            ->orderByDesc('activo')
            ->orderByDesc('created_at')
            ->get();

        return view('dte.certificados.index', compact('certificados'));
    }

    public function create()
    {
        return view('dte.certificados.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'archivo' => 'required|file|max:10240',
            'password' => 'required|string',
        ]);

        $concessionId = auth()->user()->id_concession;
        $file = $request->file('archivo');
        $password = $request->input('password');

        $contenido = file_get_contents($file->getRealPath());
        $certs = [];
        if (!openssl_pkcs12_read($contenido, $certs, $password)) {
            Flash::error('No se pudo leer el certificado. Verifique el archivo y la contrasena.');
            return back()->withInput();
        }

        $x509 = openssl_x509_parse($certs['cert']);
        if (!$x509) {
            Flash::error('No se pudo parsear el certificado X.509.');
            return back()->withInput();
        }

        $nombreTitular = $x509['subject']['CN'] ?? ($x509['subject']['O'] ?? 'Desconocido');
        $vencimiento = isset($x509['validTo_time_t'])
            ? date('Y-m-d', $x509['validTo_time_t'])
            : now()->addYear()->format('Y-m-d');

        $serialNumber = $x509['subject']['serialNumber'] ?? null;
        $rutTitular = $serialNumber ?: 'Sin RUT';

        $uuid = Str::uuid();
        $storagePath = "dte/certificados/{$concessionId}/{$uuid}.p12";
        Storage::put($storagePath, $contenido);

        $certificado = new Certificado();
        $certificado->id_concession = $concessionId;
        $certificado->rut_titular = $rutTitular;
        $certificado->nombre_titular = $nombreTitular;
        $certificado->archivo_path = $storagePath;
        $certificado->password_cifrada = $password;
        $certificado->fecha_vencimiento = $vencimiento;
        $certificado->activo = false;
        $certificado->save();

        Flash::success('Certificado subido correctamente.');
        return redirect()->route('dte.certificados.index');
    }

    public function activar(Certificado $certificado)
    {
        $concessionId = auth()->user()->id_concession;
        if ($certificado->id_concession !== $concessionId) {
            abort(403);
        }

        if (!$certificado->isVigente()) {
            Flash::error('No se puede activar un certificado vencido.');
            return back();
        }

        Certificado::where('id_concession', $concessionId)
            ->where('activo', true)
            ->update(['activo' => false]);

        $certificado->update(['activo' => true]);

        Flash::success('Certificado activado correctamente.');
        return redirect()->route('dte.certificados.index');
    }

    public function destroy(Certificado $certificado)
    {
        $concessionId = auth()->user()->id_concession;
        if ($certificado->id_concession !== $concessionId) {
            abort(403);
        }

        if ($certificado->activo) {
            Flash::error('No se puede eliminar un certificado activo. Desactivelo primero.');
            return back();
        }

        Storage::delete($certificado->archivo_path);
        $certificado->delete();

        Flash::success('Certificado eliminado correctamente.');
        return redirect()->route('dte.certificados.index');
    }
}
