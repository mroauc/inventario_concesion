<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ContactoLanding;

class LandingController extends Controller
{
    public function home()
    {
        return view('landing.home', [
            'title'       => 'ROAVAL LIMITADA – Servicio Técnico Autorizado',
            'description' => 'Servicio técnico autorizado de línea blanca en Linares y Región del Maule. Reparamos Refrigeradores, Lavadoras, Secadoras y Calefones.',
        ]);
    }

    public function repuestos()
    {
        return view('landing.repuestos', [
            'title'       => 'Repuestos y Accesorios – ROAVAL LIMITADA',
            'description' => 'Venta de repuestos y accesorios para línea blanca: lavadoras, refrigeradores, secadoras, calefones y más. Marcas Electrolux, Fensa, Mademsa.',
        ]);
    }

    public function conocenos()
    {
        return view('landing.conocenos', [
            'title'       => 'Conócenos – ROAVAL LIMITADA',
            'description' => 'Somos servicio técnico autorizado SAI para las marcas Electrolux, Fensa y Mademsa en la Provincia de Linares y Cauquenes, Región del Maule.',
        ]);
    }

    public function contacto()
    {
        return view('landing.contacto', [
            'title'       => 'Contacto – ROAVAL LIMITADA',
            'description' => 'Contáctanos para solicitar un servicio técnico o consultar sobre repuestos. Estamos en Lautaro Nº 533, Linares.',
        ]);
    }

    public function contactoEnviar(Request $request)
    {
        $request->validate([
            'nombre'              => 'required|string|max:100',
            'email'               => 'required|email|max:100',
            'telefono'            => 'nullable|string|max:20',
            'asunto'              => 'nullable|string|max:150',
            'mensaje'             => 'required|string|max:2000',
            'g-recaptcha-response' => 'required',
        ], [
            'g-recaptcha-response.required' => 'Por favor verifica que no eres un robot.',
        ]);

        $response = \Http::asForm()->post('https://www.google.com/recaptcha/api/siteverify', [
            'secret'   => config('services.recaptcha.secret_key'),
            'response' => $request->input('g-recaptcha-response'),
            'remoteip' => $request->ip(),
        ]);

        if (!($response->json('success'))) {
            return back()->withErrors(['g-recaptcha-response' => 'La verificación del captcha falló. Inténtalo de nuevo.'])->withInput();
        }

        ContactoLanding::create($request->only('nombre', 'email', 'telefono', 'asunto', 'mensaje'));

        return back()->with('success', '¡Mensaje enviado! Nos pondremos en contacto pronto.');
    }

    public function mensajes()
    {
        $mensajes = ContactoLanding::latest()->paginate(20);
        return view('contactos_landing.index', compact('mensajes'));
    }

    public function mensajeMarcarLeido(ContactoLanding $contacto)
    {
        $contacto->update(['leido' => !$contacto->leido]);
        return back();
    }

    public function mensajeDestroy(ContactoLanding $contacto)
    {
        $contacto->delete();
        return back()->with('success', 'Mensaje eliminado.');
    }
}
