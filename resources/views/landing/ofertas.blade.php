@extends('layouts.landing')

@section('content')

{{-- Header + intro en un solo bloque: el h1 y el h2 decían lo mismo --}}
<section class="page-header page-header--compact">
    <div class="container">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-2">
                <li class="breadcrumb-item"><a href="{{ route('landing.home') }}">Inicio</a></li>
                <li class="breadcrumb-item active">Ofertas</li>
            </ol>
        </nav>
        <h1 class="page-header__title mb-2">Electrodomésticos a Precio Rebajado</h1>
        <p class="page-header__lead mb-0">
            Los mismos productos del retail, <strong>más baratos</strong>.
            Stock limitado: consúltanos por WhatsApp antes de que se venda.
        </p>
    </div>
</section>

{{-- Catálogo --}}
<section class="py-5 bg-light" style="margin-bottom: 50px;">
    <div class="container">
        @if($ofertas->isEmpty())
            <div class="text-center py-5">
                <i class="fas fa-tags fa-3x text-muted mb-3"></i>
                <h3 class="h5">No hay ofertas publicadas en este momento</h3>
                <p class="text-muted mb-4">Vuelve pronto o escríbenos para consultar por disponibilidad.</p>
                <a href="https://wa.me/56933223194" target="_blank" rel="noopener" class="btn btn-whatsapp btn-lg px-4">
                    <i class="fab fa-whatsapp me-2"></i> Consultar por WhatsApp
                </a>
            </div>
        @else
            <div class="row g-4">
                @foreach($ofertas as $oferta)
                    @php $fotos = $oferta->fotos ?? []; @endphp
                    <div class="col-sm-6 col-lg-4">
                        <article class="oferta-card h-100 {{ $oferta->vendido ? 'oferta-card--vendida' : '' }}">
                            <div class="oferta-card__media">
                                @if($oferta->fotoPrincipal())
                                    {{-- Con más de una foto la imagen abre el modal con la galería --}}
                                    <img src="{{ asset('storage/' . $oferta->fotoPrincipal()) }}"
                                         alt="{{ $oferta->nombre }}" class="oferta-card__img" loading="lazy"
                                         @if(count($fotos) > 1 && !$oferta->vendido)
                                             role="button" tabindex="0"
                                             data-bs-toggle="modal" data-bs-target="#galeria{{ $oferta->id }}"
                                         @endif>

                                    @if(count($fotos) > 1 && !$oferta->vendido)
                                        <button type="button" class="oferta-card__contador"
                                                data-bs-toggle="modal" data-bs-target="#galeria{{ $oferta->id }}">
                                            <i class="fas fa-images me-1"></i>1/{{ count($fotos) }}
                                        </button>
                                    @endif
                                @else
                                    <div class="oferta-card__img oferta-card__img--placeholder">
                                        <i class="fas fa-image"></i>
                                    </div>
                                @endif

                                @if($oferta->vendido)
                                    <span class="oferta-badge-vendido">Vendido</span>
                                @endif
                            </div>

                            <div class="oferta-card__body">
                                <h3 class="oferta-card__title">{{ $oferta->nombre }}</h3>

                                {{-- ponytail: fecha numérica; el locale de la app es 'en' y
                                     translatedFormat() escribiría los meses en inglés. --}}
                                <p class="oferta-card__fecha">
                                    <i class="far fa-calendar-alt me-1"></i>
                                    Publicado el
                                    <time datetime="{{ $oferta->created_at->toDateString() }}">
                                        {{ $oferta->created_at->format('d/m/Y') }}
                                    </time>
                                </p>

                                <p class="oferta-card__precio">${{ number_format($oferta->precio, 0, ',', '.') }}</p>

                                @if($oferta->descripcion)
                                    <p class="oferta-card__text">{{ Str::limit($oferta->descripcion, 120) }}</p>
                                @endif

                                @unless($oferta->vendido)
                                    <a href="https://wa.me/56933223194?text={{ urlencode('Hola, me interesa: ' . $oferta->nombre) }}"
                                       target="_blank" rel="noopener" class="btn btn-whatsapp btn-sm mt-auto">
                                        <i class="fab fa-whatsapp me-2"></i> Consultar
                                    </a>
                                @endunless
                            </div>
                        </article>

                        {{-- Galería: carrusel de Bootstrap 5, ya cargado en el layout --}}
                        @if(count($fotos) > 1 && !$oferta->vendido)
                            <div class="modal fade oferta-modal" id="galeria{{ $oferta->id }}" tabindex="-1"
                                 aria-labelledby="galeriaTitulo{{ $oferta->id }}" aria-hidden="true">
                                <div class="modal-dialog modal-lg modal-dialog-centered">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title" id="galeriaTitulo{{ $oferta->id }}">
                                                {{ $oferta->nombre }}
                                            </h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                                        </div>

                                        <div class="modal-body p-0">
                                            <div id="carrusel{{ $oferta->id }}" class="carousel slide" data-bs-ride="false">
                                                <div class="carousel-inner">
                                                    @foreach($fotos as $i => $foto)
                                                        <div class="carousel-item {{ $i === 0 ? 'active' : '' }}">
                                                            <img src="{{ asset('storage/' . $foto) }}"
                                                                 alt="{{ $oferta->nombre }} — foto {{ $i + 1 }}"
                                                                 class="oferta-modal__img" loading="lazy">
                                                        </div>
                                                    @endforeach
                                                </div>

                                                <button class="carousel-control-prev" type="button"
                                                        data-bs-target="#carrusel{{ $oferta->id }}" data-bs-slide="prev">
                                                    <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                                                    <span class="visually-hidden">Anterior</span>
                                                </button>
                                                <button class="carousel-control-next" type="button"
                                                        data-bs-target="#carrusel{{ $oferta->id }}" data-bs-slide="next">
                                                    <span class="carousel-control-next-icon" aria-hidden="true"></span>
                                                    <span class="visually-hidden">Siguiente</span>
                                                </button>

                                                <div class="carousel-indicators">
                                                    @foreach($fotos as $i => $foto)
                                                        <button type="button" data-bs-target="#carrusel{{ $oferta->id }}"
                                                                data-bs-slide-to="{{ $i }}"
                                                                class="{{ $i === 0 ? 'active' : '' }}"
                                                                aria-label="Foto {{ $i + 1 }}"
                                                                @if($i === 0) aria-current="true" @endif></button>
                                                    @endforeach
                                                </div>
                                            </div>
                                        </div>

                                        <div class="modal-footer justify-content-between">
                                            <span class="oferta-card__precio mb-0">
                                                ${{ number_format($oferta->precio, 0, ',', '.') }}
                                            </span>
                                            @unless($oferta->vendido)
                                                <a href="https://wa.me/56933223194?text={{ urlencode('Hola, me interesa: ' . $oferta->nombre) }}"
                                                   target="_blank" rel="noopener" class="btn btn-whatsapp">
                                                    <i class="fab fa-whatsapp me-2"></i> Consultar por WhatsApp
                                                </a>
                                            @endunless
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>

            @if($ofertas->hasPages())
                <div class="d-flex justify-content-center mt-5">
                    {{ $ofertas->links('pagination::bootstrap-4') }}
                </div>
            @endif
        @endif
    </div>
</section>

@endsection
