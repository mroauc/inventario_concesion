<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ContactoLanding;
use App\Models\LandingVisit;
use App\Models\Oferta;

class LandingController extends Controller
{
    public function home()
    {
        return view('landing.home', [
            'ofertasDestacadas' => Oferta::where('estado', true)
                ->where('vendido', false)
                ->latest()
                ->take(3)
                ->get(),
            'title'       => 'ROAVAL LIMITADA – Servicio Técnico Autorizado',
            'description' => 'Servicio técnico autorizado de línea blanca en Linares y Región del Maule. Reparamos Refrigeradores, Lavadoras, Secadoras y Calefones.',
        ]);
    }

    public function ofertas()
    {
        // ponytail: landing mono-concesión. Filtrar por dominio si algún día hay más de una.
        $ofertas = Oferta::where('estado', true)
            ->orderBy('vendido')            // disponibles primero, vendidas al final
            ->orderByDesc('created_at')
            ->paginate(12);

        return view('landing.ofertas', [
            'ofertas'     => $ofertas,
            'title'       => 'Ofertas – Electrodomésticos a precio rebajado | ROAVAL LIMITADA',
            'description' => 'Electrodomésticos y línea blanca a precios más bajos que el retail. Lavadoras, refrigeradores, secadoras y más en Linares, Región del Maule.',
        ]);
    }

    public function repuestos()
    {
        // Definido acá (y no en la vista) porque el JSON-LD del @push('head') también lo usa,
        // y el <head> se renderiza antes que el @php de la sección.
        $categorias = [
            [
                'icon'   => 'fa-tshirt',
                'nombre' => 'Lavadoras',
                'color'  => 'primary',
                'items'  => [
                    'Bombas de desagüe (Electrolux, Daewoo, Askol)',
                    'Motores de lavado (7 a 20 kg)',
                    'Timers y tarjetas electrónicas (PCB)',
                    'Electroválvulas de agua fría',
                    'Correas de transmisión (M-21, M-20.5, M-28)',
                    'Mangueras de desagüe y alimentación',
                    'Rodamientos y transmisiones (dumping)',
                    'Cables, conectores y presostatos',
                ],
            ],
            [
                'icon'   => 'fa-temperature-low',
                'nombre' => 'Refrigeradores',
                'color'  => 'info',
                'items'  => [
                    'Termostatos (1 puerta y No Frost)',
                    'Sensores de temperatura NTC',
                    'Timers de deshielo (Sankyo, azul)',
                    'Ventiladores No Frost',
                    'Resistencias de deshielo',
                    'Tarjetas electrónicas de control (Altus)',
                    'Gavetas y estantes evaporador',
                    'Interruptores de puerta y relays',
                ],
            ],
            [
                'icon'   => 'fa-burn',
                'nombre' => 'Cocinas a Gas',
                'color'  => 'warning',
                'items'  => [
                    'Quemadores y copas (SABAF y CEMCO)',
                    'Tapas y rejillas de quemadores',
                    'Perillas de control (Sindelen, Titanium, Volcano)',
                    'Conmutadores de horno',
                    'Cables y armados de horno (16 A)',
                    'Flexibles de gas (1/2×1/2, 3/8×1/2)',
                    'Lámparas de horno y bujías',
                    'Burletes y termocuplas de horno',
                ],
            ],
            [
                'icon'   => 'fa-wind',
                'nombre' => 'Secadoras',
                'color'  => 'success',
                'items'  => [
                    'Correas de transmisión (1915, 1930, 1975, 1980)',
                    'Ventiladores y hélices',
                    'Termostatos y termofusibles',
                    'Juntas de tambor (065 y estándar)',
                    'Filtros de pelusa y rejillas',
                    'Mangueras y ductos de salida de aire',
                    'Condensadores y capacitores',
                    'Tarjetas electrónicas (PCB Solare)',
                ],
            ],
            [
                'icon'   => 'fa-fire',
                'nombre' => 'Calefones',
                'color'  => 'danger',
                'items'  => [
                    'Membranas (Junkers, Neckar, Mademsa, Vitality, Splendid)',
                    'Módulos de encendido (válvula Tonka y flow switch)',
                    'Electroválvulas (15 mm, blanco y negro)',
                    'Flowswitch con despiche',
                    'Termocuplas (termo par H-200)',
                    'Cajas de pilas (Altus, Neckar, Junkers)',
                    'Interruptores de agua (2P y 3P)',
                    'Válvulas de gas y caño venturi',
                ],
            ],
            [
                'icon'   => 'fa-fire-alt',
                'nombre' => 'Estufas a Parafina',
                'color'  => 'secondary',
                'items'  => [
                    'Mechas (Foguita Pro, Omni 230, Fiamma, KS27, Potenza, Volcano, Nacional)',
                    'Perillas y portaperillas (Mademsa 5 y 15 kg, Volcano, Fensa)',
                    'Quemadores y vaporizadores (chico y grande)',
                    'Filtros de aceite (540, 950/590/990)',
                    'Encendedores a pilas y piezoeléctricos',
                    'Jeringas plásticas y trasvasijadores',
                    'Guías de mecha (F-1120+, Fiamma Pro, 720+)',
                    'Estanques, cartuchos y bobinas de encendido',
                ],
            ],
        ];

        return view('landing.repuestos', [
            'categorias'  => $categorias,
            'title'       => 'Repuestos de Línea Blanca en Chile – Envíos a Todo el País | ROAVAL',
            'description' => 'Repuestos para lavadoras, refrigeradores, secadoras, cocinas, calefones y estufas a parafina. Electrolux, Fensa, Mademsa. Despacho a todo Chile: Santiago, Valparaíso, Concepción, Temuco y más.',
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

    public function trackClick(Request $request, $tipo)
    {
        if (!in_array($tipo, ['whatsapp', 'instagram', 'llamada'])) {
            abort(404);
        }

        LandingVisit::create([
            'pagina'     => 'click:' . $tipo,
            'ip'         => $request->ip(),
            'user_agent' => $request->userAgent(),
            'referrer'   => $request->headers->get('referer'),
        ]);

        return response()->noContent();
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
