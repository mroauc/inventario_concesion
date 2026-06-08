@extends('layouts.landing')

@section('content')

{{-- Page Header --}}
<section class="page-header">
    <div class="container">
        <h1 class="page-header__title">Conócenos</h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="{{ route('landing.home') }}">Inicio</a></li>
                <li class="breadcrumb-item active">Conócenos</li>
            </ol>
        </nav>
    </div>
</section>

{{-- Quiénes somos --}}
<section class="py-6">
    <div class="container">
        <div class="row align-items-center g-5">
            <div class="col-lg-6">
                <span class="section-eyebrow">Quiénes somos</span>
                <h2 class="section-title mt-2 mb-4">Servicio Técnico Autorizado <span class="text-brand">ROAVAL</span></h2>
                <p class="text-muted mb-3">
                    Somos una empresa dedicada al servicio de asistencia integral (SAI) para las marcas
                    <strong>Electrolux, Fensa</strong> y <strong>Mademsa</strong>, atendiendo consultas
                    y reparaciones de productos de línea blanca tanto dentro como fuera de garantía.
                </p>
                <p class="text-muted mb-4">
                    Con más de años de experiencia en la Región del Maule, contamos con técnicos
                    certificados y repuestos originales para entregar el mejor servicio a nuestros clientes,
                    ya sea en su hogar o en nuestro taller en Linares.
                </p>

                <div class="row g-3">
                    <div class="col-sm-6">
                        <div class="stat-card">
                            <div class="stat-card__number text-brand">SAI</div>
                            <div class="stat-card__label">Servicio de Asistencia Integral</div>
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class="stat-card">
                            <div class="stat-card__number text-brand">11</div>
                            <div class="stat-card__label">Comunas atendidas</div>
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class="stat-card">
                            <div class="stat-card__number text-brand">3</div>
                            <div class="stat-card__label">Marcas autorizadas</div>
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class="stat-card">
                            <div class="stat-card__number text-brand">2</div>
                            <div class="stat-card__label">Provincias</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-6">
                <div class="map-placeholder rounded-3">
                    <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d6468.229509394839!2d-71.5942767!3d-35.846189599999995!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x9665f542b6fe03f3%3A0x8f283c5ecd4c7a22!2sROAVAL%20LIMITADA!5e0!3m2!1ses-419!2scl!4v1780873496971!5m2!1ses-419!2scl" width="600" height="450" style="border:0;" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
                </div>
            </div>
        </div>
    </div>
</section>

{{-- Marcas autorizadas --}}
<section class="py-5 bg-light">
    <div class="container">
        <div class="text-center mb-5">
            <h2 class="section-title">Marcas Autorizadas</h2>
            <p class="text-muted">Somos representantes autorizados SAI para estas marcas en la Región del Maule.</p>
        </div>
        <div class="row justify-content-center g-4">
            @php
            $marcas = [
                ['nombre' => 'SAI',       'desc' => 'Servicio de Asistencia Integral — organismo coordinador del servicio técnico.', 'img_url' => "sai-logo.webp", 'width' => '100px'],
                ['nombre' => 'Electrolux','desc' => 'Líder mundial en electrodomésticos. Reparaciones certificadas.', 'img_url' => "electrolux-logo.png", 'width' => '70%'],
                ['nombre' => 'Mademsa',   'desc' => 'Marca chilena con larga historia en línea blanca nacional.', 'img_url' => "logo-mademsa.png", 'width' => '70%'],
                ['nombre' => 'Fensa',     'desc' => 'Electrodomésticos de alto rendimiento para el hogar chileno.', 'img_url' => "fensa-logo.png", 'width' => '70%'],
            ];
            @endphp
            @foreach($marcas as $m)
            <div class="col-sm-6 col-lg-3">
                <div class="brand-card text-center h-100">
                    <img width="{{$m['width']}}" src="{{asset('./landing/images/').'/'.$m['img_url']}}" alt="">
                    {{-- <div class="brand-card__logo mb-3">{{ $m['nombre'] }}</div> --}}
                    <p class="small text-muted mb-0">{{ $m['desc'] }}</p>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</section>

{{-- Cobertura --}}
<section class="py-6">
    <div class="container">
        <div class="text-center mb-5">
            <h2 class="section-title">Área de Cobertura</h2>
            <p class="text-muted">Atendemos a domicilio en las siguientes comunas de la Región del Maule.</p>
        </div>

        <div class="row g-4 justify-content-center">
            <div class="col-md-5">
                <div class="coverage-box">
                    <h5 class="coverage-box__title">
                        <i class="fas fa-map-pin me-2"></i>Provincia de Linares
                    </h5>
                    <ul class="list-unstyled coverage-list mb-0">
                        @foreach(['Linares (Casa Matriz)', 'Parral', 'Longaví', 'Colbún', 'Retiro', 'Yerbas Buenas', 'San Javier', 'Villa Alegre'] as $com)
                        <li><i class="fas fa-check text-success me-2 small"></i>{{ $com }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
            <div class="col-md-5">
                <div class="coverage-box">
                    <h5 class="coverage-box__title">
                        <i class="fas fa-map-pin me-2"></i>Provincia de Cauquenes
                    </h5>
                    <ul class="list-unstyled coverage-list mb-0">
                        @foreach(['Cauquenes', 'Chanco', 'Pelluhue'] as $com)
                        <li><i class="fas fa-check text-success me-2 small"></i>{{ $com }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
    </div>
</section>

{{-- Horarios --}}
<section class="py-5 bg-light">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="text-center mb-4">
                    <h2 class="section-title">Horarios de Atención</h2>
                </div>
                <div class="table-responsive">
                    <table class="table table-borderless schedule-table text-center">
                        <thead>
                            <tr>
                                <th>Día</th>
                                <th>Horario</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td class="fw-semibold">Lunes</td>
                                <td>9:00 – 18:30</td>
                            </tr>
                            <tr>
                                <td class="fw-semibold">Martes</td>
                                <td>9:00 – 18:30</td>
                            </tr>
                            <tr>
                                <td class="fw-semibold">Miércoles</td>
                                <td>9:00 – 18:30</td>
                            </tr>
                            <tr>
                                <td class="fw-semibold">Jueves</td>
                                <td>9:00 – 18:30</td>
                            </tr>
                            <tr>
                                <td class="fw-semibold">Viernes</td>
                                <td>9:00 – 17:30</td>
                            </tr>
                            <tr>
                                <td class="fw-semibold">Sábado</td>
                                <td>9:15 – 13:00</td>
                            </tr>
                            <tr>
                                <td class="fw-semibold">Domingo</td>
                                <td class="text-muted">Cerrado</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</section>

{{-- CTA --}}
<section class="py-5 bg-brand-dark text-white text-center">
    <div class="container">
        <h2 class="mb-3 fw-bold">¿Necesitas un servicio técnico?</h2>
        <p class="opacity-75 mb-4">Agenda tu visita o consúltanos sin compromiso.</p>
        <a href="{{ route('landing.contacto') }}" class="btn btn-light btn-lg px-5">
            <i class="fas fa-envelope me-2"></i>Contáctanos
        </a>
    </div>
</section>

@endsection
