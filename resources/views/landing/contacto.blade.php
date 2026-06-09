@extends('layouts.landing')

@section('content')

{{-- Page Header --}}
<section class="page-header">
    <div class="container">
        <h1 class="page-header__title">Contacto</h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="{{ route('landing.home') }}">Inicio</a></li>
                <li class="breadcrumb-item active">Contacto</li>
            </ol>
        </nav>
    </div>
</section>

<section class="py-6">
    <div class="container">

        @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show mb-4" role="alert">
            <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        @endif

        <div class="row g-5">

            {{-- Columna: formulario --}}
            <div class="col-lg-7">
                <h2 class="section-title mb-2">Servicio Autorizado</h2>
                <p class="text-muted mb-4">
                    Completa el formulario y te responderemos a la brevedad, o contáctanos
                    directamente por teléfono o WhatsApp.
                </p>

                <form action="{{ route('landing.contacto.enviar') }}" method="POST" novalidate>
                    @csrf
                    <div class="row g-3">
                        <div class="col-sm-6">
                            <label for="nombre" class="form-label fw-semibold">
                                Tu nombre <span class="text-danger">*</span>
                            </label>
                            <input type="text" name="nombre" id="nombre"
                                   class="form-control @error('nombre') is-invalid @enderror"
                                   value="{{ old('nombre') }}"
                                   placeholder="Juan Pérez" required>
                            @error('nombre')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-sm-6">
                            <label for="email" class="form-label fw-semibold">
                                Correo electrónico <span class="text-danger">*</span>
                            </label>
                            <input type="email" name="email" id="email"
                                   class="form-control @error('email') is-invalid @enderror"
                                   value="{{ old('email') }}"
                                   placeholder="correo@ejemplo.cl" required>
                            @error('email')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-12">
                            <label for="asunto" class="form-label fw-semibold">Asunto</label>
                            <input type="text" name="asunto" id="asunto"
                                   class="form-control @error('asunto') is-invalid @enderror"
                                   value="{{ old('asunto') }}"
                                   placeholder="Ej: Reparación de lavadora">
                            @error('asunto')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-12">
                            <label for="mensaje" class="form-label fw-semibold">
                                Tu mensaje <span class="text-danger">*</span>
                            </label>
                            <textarea name="mensaje" id="mensaje" rows="5"
                                      class="form-control @error('mensaje') is-invalid @enderror"
                                      placeholder="Describe el problema o consulta..." required>{{ old('mensaje') }}</textarea>
                            @error('mensaje')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-12">
                            <div class="g-recaptcha" data-sitekey="{{ config('services.recaptcha.site_key') }}"></div>
                            @error('g-recaptcha-response')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-12">
                            <button type="submit" class="btn btn-brand-primary btn-lg px-5">
                                <i class="fas fa-paper-plane me-2"></i>Enviar Mensaje
                            </button>
                        </div>
                    </div>
                </form>
            </div>

            {{-- Columna: datos --}}
            <div class="col-lg-5">
                <div class="contact-info-card h-100">
                    <h5 class="fw-bold mb-4 text-brand">Información de Contacto</h5>

                    <ul class="list-unstyled contact-info-list">
                        <li>
                            <i class="fas fa-map-marker-alt contact-info-icon"></i>
                            <div>
                                <strong>Dirección</strong>
                                <p class="mb-0 text-muted">Lautaro Nº 533, Linares<br>Región del Maule, Chile</p>
                            </div>
                        </li>
                        <li>
                            <i class="fas fa-phone contact-info-icon"></i>
                            <div>
                                <strong>Teléfono</strong>
                                <p class="mb-0">
                                    <a href="tel:+5673263342" class="text-muted text-decoration-none">(73) 2633420</a>
                                </p>
                            </div>
                        </li>
                        <li>
                            <i class="fab fa-whatsapp contact-info-icon text-success"></i>
                            <div>
                                <strong>WhatsApp</strong>
                                <p class="mb-0">
                                    <a href="https://wa.me/56933223194" target="_blank" rel="noopener"
                                       class="text-muted text-decoration-none">+56 9 3322 3194</a>
                                </p>
                            </div>
                        </li>
                        <li>
                            <i class="fas fa-envelope contact-info-icon"></i>
                            <div>
                                <strong>Email</strong>
                                <p class="mb-0">
                                    <a href="mailto:contactosai@roaval.com" class="text-muted text-decoration-none">
                                        contactosai@roaval.com
                                    </a>
                                </p>
                            </div>
                        </li>
                        <li>
                            <i class="fas fa-clock contact-info-icon"></i>
                            <div>
                                <strong>Horario</strong>
                                <p class="mb-0 text-muted">
                                    Lun–Jue: 9:00–18:30<br>
                                    Vie: 9:00–17:30<br>
                                    Sáb: 9:15–13:00<br>
                                    Domingo: Cerrado
                                </p>
                            </div>
                        </li>
                    </ul>

                    <div class="mt-4 pt-4 border-top d-flex gap-2">
                        <a href="https://wa.me/56933223194" target="_blank" rel="noopener"
                           class="btn btn-whatsapp flex-fill" title="WhatsApp">
                            <i class="fab fa-whatsapp"></i>
                        </a>
                        <a href="https://www.instagram.com/roaval_serviciotecnico" target="_blank" rel="noopener"
                           class="btn btn-instagram flex-fill" title="Instagram">
                            <i class="fab fa-instagram"></i>
                        </a>
                    </div>
                </div>
            </div>

        </div>
    </div>
</section>

@endsection

@push('scripts')
<script src="https://www.google.com/recaptcha/api.js" async defer></script>
@endpush
