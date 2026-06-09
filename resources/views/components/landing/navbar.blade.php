@php
    $navLinks = [
        ['route' => 'landing.home',      'label' => 'Inicio'],
        ['route' => 'landing.repuestos', 'label' => 'Repuestos / Accesorios'],
        ['route' => 'landing.conocenos', 'label' => 'Conócenos'],
        ['route' => 'landing.contacto',  'label' => 'Contacto'],
    ];
    $current = Route::currentRouteName();
@endphp

<nav class="navbar navbar-expand-lg navbar-dark landing-navbar sticky-top">
    <div class="container">
        <a class="navbar-brand d-flex align-items-center gap-2" href="{{ route('landing.home') }}">
            <img src="{{ asset('./landing/images/transparente_horizontal.png') }}" alt="Roaval" style="height: 40px;">
            {{-- <i class="fas fa-wrench fs-5"></i>
            <span class="fw-bold">ROAVAL <span class="fw-light">LIMITADA</span></span> --}}
        </a>

        <button class="navbar-toggler border-0" type="button"
                data-bs-toggle="collapse" data-bs-target="#landingNav"
                aria-controls="landingNav" aria-expanded="false" aria-label="Abrir menú">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="landingNav">
            <ul class="navbar-nav ms-auto gap-1">
                @foreach($navLinks as $link)
                <li class="nav-item">
                    <a class="nav-link px-3 {{ $current === $link['route'] ? 'active' : '' }}"
                       href="{{ route($link['route']) }}">
                        {{ $link['label'] }}
                    </a>
                </li>
                @endforeach
            </ul>

            <a href="https://wa.me/56933223194" target="_blank" rel="noopener"
               class="ms-3 d-none d-lg-inline-flex align-items-center text-white fs-5 text-decoration-none" title="WhatsApp">
                <i class="fab fa-whatsapp"></i>
            </a>
            <a href="https://www.instagram.com/roaval_serviciotecnico" target="_blank" rel="noopener"
               class="ms-3 d-none d-lg-inline-flex align-items-center text-white fs-5 text-decoration-none" title="Instagram">
                <i class="fab fa-instagram"></i>
            </a>
        </div>
    </div>
</nav>
