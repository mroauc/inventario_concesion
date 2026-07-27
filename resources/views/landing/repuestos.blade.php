@extends('layouts.landing')

{{-- Schema.org: declara a Google que vendemos repuestos con cobertura en todo Chile --}}
@push('head')
<script type="application/ld+json">
{
  "@context": "https://schema.org",
  "@type": "Store",
  "name": "ROAVAL LIMITADA – Repuestos de Línea Blanca",
  "description": "Venta de repuestos y accesorios para lavadoras, refrigeradores, secadoras, cocinas a gas, calefones y estufas a parafina. Envíos a todo Chile.",
  "url": "{{ url()->current() }}",
  "telephone": "+56933223194",
  "address": {
    "@type": "PostalAddress",
    "streetAddress": "Lautaro Nº 533",
    "addressLocality": "Linares",
    "addressRegion": "Región del Maule",
    "addressCountry": "CL"
  },
  "areaServed": {
    "@type": "Country",
    "name": "Chile"
  },
  "makesOffer": [
    @foreach($categorias ?? [] as $i => $cat)
    {
      "@type": "Offer",
      "itemOffered": {
        "@type": "Product",
        "name": "Repuestos para {{ $cat['nombre'] }}",
        "category": "Repuestos de línea blanca"
      },
      "availableAtOrFrom": { "@type": "Country", "name": "Chile" }
    }@if(!$loop->last),@endif
    @endforeach
  ]
}
</script>
@endpush

@section('content')

{{-- Page Header --}}
<section class="page-header">
    <div class="container">
        <h1 class="page-header__title">Repuestos y Accesorios de Línea Blanca en Chile</h1>
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
                <h2 class="section-title mb-3">Repuestos Originales para Línea Blanca en Chile</h2>
                <p class="text-muted">
                    Contamos con un amplio stock de repuestos y accesorios para las marcas
                    <strong>Electrolux, Fensa</strong> y <strong>Mademsa</strong>, además de repuestos para
                    calefones, estufas a parafina y otras marcas del mercado chileno.
                </p>
                <p class="text-muted mb-0">
                    <strong>Enviamos repuestos a todo Chile.</strong> Despachamos por courier a cualquier
                    ciudad del país — Santiago, Valparaíso, Concepción, Temuco, Antofagasta, La Serena,
                    Puerto Montt, Iquique, Rancagua, Talca, Chillán, Arica, Punta Arenas y más.
                    Visítanos en nuestro local en Linares o consúltanos vía WhatsApp y coordinamos el envío.
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
