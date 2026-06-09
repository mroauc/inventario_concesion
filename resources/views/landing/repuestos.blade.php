@extends('layouts.landing')

@section('content')

{{-- Page Header --}}
<section class="page-header">
    <div class="container">
        <h1 class="page-header__title">Repuestos y Accesorios</h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="{{ route('landing.home') }}">Inicio</a></li>
                <li class="breadcrumb-item active">Repuestos / Accesorios</li>
            </ol>
        </nav>
    </div>
</section>

{{-- Intro --}}
<section class="py-5">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8 text-center">
                <h2 class="section-title mb-3">Repuestos Originales para Línea Blanca</h2>
                <p class="text-muted">
                    Contamos con un amplio stock de repuestos y accesorios para las marcas
                    <strong>Electrolux, Fensa</strong> y <strong>Mademsa</strong>, además de repuestos para
                    calefones, estufas a parafina y otras marcas del mercado chileno.
                    Visítanos en nuestro local o consúltanos vía WhatsApp.
                </p>
            </div>
        </div>
    </div>
</section>

{{-- Categorías --}}
<section class="py-5 bg-light">
    <div class="container">
        <div class="text-center mb-5">
            <h2 class="section-title">Categorías de Repuestos</h2>
        </div>

        @php
        $categorias = [
            [
                'icon'    => 'fa-tshirt',
                'nombre'  => 'Lavadoras',
                'color'   => 'primary',
                'items'   => [
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
                'icon'    => 'fa-temperature-low',
                'nombre'  => 'Refrigeradores',
                'color'   => 'info',
                'items'   => [
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
                'icon'    => 'fa-burn',
                'nombre'  => 'Cocinas a Gas',
                'color'   => 'warning',
                'items'   => [
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
                'icon'    => 'fa-wind',
                'nombre'  => 'Secadoras',
                'color'   => 'success',
                'items'   => [
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
                'icon'    => 'fa-fire',
                'nombre'  => 'Calefones',
                'color'   => 'danger',
                'items'   => [
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
                'icon'    => 'fa-fire-alt',
                'nombre'  => 'Estufas a Parafina',
                'color'   => 'secondary',
                'items'   => [
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
        @endphp

        <div class="row g-4">
            @foreach($categorias as $cat)
            <div class="col-sm-6 col-lg-4">
                <div class="repuesto-card h-100">
                    <div class="repuesto-card__header bg-{{ $cat['color'] }} bg-opacity-10">
                        <i class="fas {{ $cat['icon'] }} repuesto-card__icon text-{{ $cat['color'] }}"></i>
                        <h5 class="repuesto-card__title mb-0">{{ $cat['nombre'] }}</h5>
                    </div>
                    <div class="repuesto-card__body">
                        <ul class="list-unstyled mb-0">
                            @foreach($cat['items'] as $item)
                            <li class="repuesto-item">
                                <i class="fas fa-check-circle text-success me-2 small"></i>{{ $item }}
                            </li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</section>

{{-- Marcas --}}
<section class="py-5">
    <div class="container">
        <div class="text-center mb-4">
            <h2 class="section-title">Marcas Disponibles</h2>
            <p class="text-muted">Repuestos originales y compatibles para las principales marcas del mercado.</p>
        </div>
        <div class="row justify-content-center g-3">
            @foreach(['SAI', 'Electrolux', 'Mademsa', 'Fensa'] as $brand)
            <div class="col-6 col-md-3 text-center">
                <div class="brand-badge">{{ $brand }}</div>
            </div>
            @endforeach
        </div>
    </div>
</section>

{{-- CTA --}}
<section class="py-5 bg-brand-dark text-white text-center">
    <div class="container">
        <h2 class="mb-3 fw-bold">¿No encontraste el repuesto que necesitas?</h2>
        <p class="opacity-75 mb-4">Consúltanos directamente — podemos conseguir el repuesto que buscas.</p>
        <div class="d-flex flex-wrap justify-content-center gap-3">
            <a href="{{ route('landing.contacto') }}" class="btn btn-light btn-lg px-5">
                <i class="fas fa-envelope me-2"></i>Consultar por email
            </a>
            <a href="https://wa.me/56933223194" target="_blank" rel="noopener" class="btn btn-whatsapp btn-lg px-4" title="WhatsApp">
                <i class="fab fa-whatsapp"></i>
            </a>
            <a href="https://www.instagram.com/roaval_serviciotecnico" target="_blank" rel="noopener" class="btn btn-instagram btn-lg px-4" title="Instagram">
                <i class="fab fa-instagram"></i>
            </a>
        </div>
    </div>
</section>

@endsection
