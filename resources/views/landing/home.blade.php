@extends('layouts.landing')

@section('content')

<style>
    .hero-slide--1 {
        background-image: url("{{ asset('landing/images/empresa_foto.webp') }}");
    }
</style>

{{-- ═══════════════════════════════════════════════════
    HERO
════════════════════════════════════════════════════ --}}
<section class="hero-section">
    <div id="heroCarousel" class="carousel slide carousel-fade" data-bs-ride="carousel">
        <div class="carousel-inner">

            <div class="carousel-item active">
                <div class="hero-slide hero-slide--1">
                    <div class="hero-overlay"></div>
                    <div class="container h-100 d-flex align-items-center">
                        <div class="hero-content text-white">
                            <span class="hero-badge mb-3">Servicio Técnico Autorizado</span>
                            <h1 class="hero-title">Bienvenidos a <strong>ROAVAL</strong></h1>
                            <p class="hero-subtitle">
                                Reparamos productos de línea blanca dentro y fuera de garantía,
                                <br class="d-none d-md-block">en tu hogar o en nuestro taller.
                            </p>
                            <div class="d-flex flex-wrap gap-3 mt-4">
                                <a href="{{ route('landing.contacto') }}" class="btn btn-brand-primary btn-lg">
                                    <i class="fas fa-calendar-check me-2"></i>Solicitar Servicio
                                </a>
                                <a href="{{ route('landing.conocenos') }}" class="btn btn-outline-light btn-lg">
                                    Conócenos <i class="fas fa-arrow-right ms-2"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="carousel-item">
                <div class="hero-slide hero-slide--2">
                    <div class="hero-overlay"></div>
                    <div class="container h-100 d-flex align-items-center">
                        <div class="hero-content text-white">
                            <span class="hero-badge mb-3">Repuestos Originales</span>
                            <h1 class="hero-title">Repuestos y <strong>Accesorios</strong></h1>
                            <p class="hero-subtitle">
                                Contamos con repuestos para las principales marcas:<br>
                                Electrolux, Fensa, Mademsa y más.
                            </p>
                            <div class="d-flex flex-wrap gap-3 mt-4">
                                <a href="{{ route('landing.repuestos') }}" class="btn btn-brand-primary btn-lg">
                                    <i class="fas fa-search me-2"></i>Ver Repuestos
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>

        <button class="carousel-control-prev" type="button" data-bs-target="#heroCarousel" data-bs-slide="prev">
            <span class="carousel-control-prev-icon"></span>
            <span class="visually-hidden">Anterior</span>
        </button>
        <button class="carousel-control-next" type="button" data-bs-target="#heroCarousel" data-bs-slide="next">
            <span class="carousel-control-next-icon"></span>
            <span class="visually-hidden">Siguiente</span>
        </button>
    </div>
</section>

{{-- ═══════════════════════════════════════════════════
    BARRA RÁPIDA
════════════════════════════════════════════════════ --}}
<section class="quick-bar py-3 bg-brand-dark text-white">
    <div class="container">
        <div class="row g-2 justify-content-center text-center">
            <div class="col-6 col-md-3">
                <i class="fas fa-phone-alt me-2"></i><a href="tel:+5673263342" class="text-white text-decoration-none">(73) 2633420</a>
            </div>
            <div class="col-6 col-md-3">
                <i class="fab fa-whatsapp me-2"></i><a href="https://wa.me/56933223194" target="_blank" rel="noopener" class="text-white text-decoration-none">+56 9 3322 3194</a>
            </div>
            <div class="col-12 col-md-4">
                <i class="fas fa-clock me-2"></i>
                <span class="d-none d-sm-inline">Lun–Jue 9:00–18:30 · Vie 9:00–17:30 · Sáb 9:15–13:00</span>
                <span class="d-sm-none">Lun–Vie 9:00–18:30<br>Sáb 9:15–13:00</span>
            </div>
        </div>
    </div>
</section>

{{-- ═══════════════════════════════════════════════════
    SERVICIOS DESTACADOS
════════════════════════════════════════════════════ --}}
<section class="section-services py-6">
    <div class="container">
        <div class="text-center mb-5">
            <h2 class="section-title">Nuestros Servicios</h2>
            <p class="section-subtitle text-muted">
                Reparamos en tu hogar o en nuestro taller — con o sin garantía de fábrica.
            </p>
        </div>

        <div class="row g-4">
            @php
            $servicios = [
                ['icon' => 'fa-temperature-low', 'nombre' => 'Refrigeradores',  'desc' => 'No enfría, hace ruido, pierde frío o no enciende — lo reparamos.'],
                ['icon' => 'fa-tshirt',           'nombre' => 'Lavadoras',       'desc' => 'Vibración, no centrifuga, no drena o no enciende.'],
                ['icon' => 'fa-wind',             'nombre' => 'Secadoras',       'desc' => 'No calienta, no gira o corta el servicio — te lo dejamos listo.'],
                ['icon' => 'fa-fire',             'nombre' => 'Calefones',       'desc' => 'Sin agua caliente, llama inestable o falla de encendido.'],
                ['icon' => 'fa-blender',          'nombre' => 'Electrodomésticos de Cocina', 'desc' => 'Cocinas, hornos, lavavajillas y más.'],
                ['icon' => 'fa-tools',            'nombre' => 'Repuestos',       'desc' => 'Venta de repuestos y accesorios originales para todas las marcas.'],
            ];
            @endphp

            @foreach($servicios as $s)
            <div class="col-sm-6 col-lg-4">
                <div class="service-card h-100">
                    <div class="service-card__icon">
                        <i class="fas {{ $s['icon'] }}"></i>
                    </div>
                    <h5 class="service-card__title">{{ $s['nombre'] }}</h5>
                    <p class="service-card__text text-muted small mb-0">{{ $s['desc'] }}</p>
                </div>
            </div>
            @endforeach
        </div>

        <div class="text-center mt-5">
            <a href="{{ route('landing.contacto') }}" class="btn btn-brand-primary btn-lg px-5">
                <i class="fas fa-calendar-check me-2"></i>Solicitar una visita
            </a>
        </div>
    </div>
</section>

{{-- ═══════════════════════════════════════════════════
    MARCAS AUTORIZADAS
════════════════════════════════════════════════════ --}}
<section class="section-brands py-5 bg-light">
    <div class="container">
        <div class="text-center mb-4">
            <h2 class="section-title">Servicio Técnico Autorizado</h2>
            <p class="text-muted">Trabajamos con las principales marcas de línea blanca del mercado chileno.</p>
        </div>
        <div class="row justify-content-center align-items-center g-4">
            <div class="col-6 col-md-3 text-center">
                <img width="100px" src="{{asset('./landing/images/sai-logo.webp')}}" alt="">
            </div>
            <div class="col-6 col-md-3 text-center">
                <img width="70%" src="{{asset('./landing/images/electrolux-logo.png')}}" alt="">
            </div>
            <div class="col-6 col-md-3 text-center">
                <img width="70%" src="{{asset('./landing/images/logo-mademsa.png')}}" alt="">
            </div>
            <div class="col-6 col-md-3 text-center">
                <img width="70%" src="{{asset('./landing/images/fensa-logo.png')}}" alt="">
            </div>
        </div>
    </div>
</section>

{{-- ═══════════════════════════════════════════════════
    COBERTURA GEOGRÁFICA
════════════════════════════════════════════════════ --}}
<section class="section-coverage py-6">
    <div class="container">
        <div class="row align-items-center g-5">
            <div class="col-lg-6">
                <h2 class="section-title mb-3">Cobertura en la Región del Maule</h2>
                <p class="text-muted mb-4">
                    Atendemos a domicilio en todas las comunas de la Provincia de Linares
                    y Cauquenes.
                </p>

                <div class="row g-3">
                    <div class="col-sm-6">
                        <div class="coverage-box">
                            <h6 class="coverage-box__title"><i class="fas fa-map-pin me-2"></i>Provincia de Linares</h6>
                            <ul class="list-unstyled mb-0 small">
                                <li>Linares <span class="text-muted">(casa matriz)</span></li>
                                <li>Parral</li>
                                <li>Longaví</li>
                                <li>Colbún</li>
                                <li>Retiro</li>
                                <li>Yerbas Buenas</li>
                                <li>San Javier</li>
                                <li>Villa Alegre</li>
                            </ul>
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class="coverage-box">
                            <h6 class="coverage-box__title"><i class="fas fa-map-pin me-2"></i>Provincia de Cauquenes</h6>
                            <ul class="list-unstyled mb-0 small">
                                <li>Cauquenes</li>
                                <li>Chanco</li>
                                <li>Pelluhue</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="map-placeholder rounded-3">
                    <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d6468.229509394839!2d-71.5942767!3d-35.846189599999995!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x9665f542b6fe03f3%3A0x8f283c5ecd4c7a22!2sROAVAL%20LIMITADA!5e0!3m2!1ses-419!2scl!4v1780873496971!5m2!1ses-419!2scl" width="100%" height="350" style="border:0;display:block;" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
                    {{-- <i class="fas fa-map-marked-alt fa-3x text-muted mb-3"></i>
                    <p class="text-muted mb-1">Lautaro Nº 533, Linares</p>
                    <small class="text-muted">Región del Maule, Chile</small> --}}
                </div>
            </div>
        </div>
    </div>
</section>

{{-- ═══════════════════════════════════════════════════
    CTA FINAL
════════════════════════════════════════════════════ --}}
<section class="section-cta py-6 bg-brand-dark text-white text-center">
    <div class="container">
        <h2 class="mb-3 fw-bold">¿Tu electrodoméstico tiene un problema?</h2>
        <p class="mb-4 opacity-75">Contáctanos hoy mismo — atendemos en toda la Región del Maule.</p>
        <div class="d-flex flex-wrap justify-content-center gap-3">
            <a href="{{ route('landing.contacto') }}" class="btn btn-light btn-lg px-5">
                <i class="fas fa-envelope me-2"></i>Envíanos un mensaje
            </a>
            <a href="https://wa.me/56933223194" target="_blank" rel="noopener" class="btn btn-whatsapp btn-lg px-5">
                <i class="fab fa-whatsapp me-2"></i>WhatsApp
            </a>
        </div>
    </div>
</section>

@endsection
