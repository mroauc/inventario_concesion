<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">

    <title>{{ $title ?? 'ROAVAL LIMITADA – Servicio Técnico Autorizado' }}</title>
    <meta name="description" content="{{ $description ?? 'Servicio técnico autorizado de línea blanca en Linares, Región del Maule.' }}">

    {{-- Open Graph --}}
    <meta property="og:type"        content="website">
    <meta property="og:title"       content="{{ $title ?? 'ROAVAL LIMITADA' }}">
    <meta property="og:description" content="{{ $description ?? 'Servicio técnico autorizado de línea blanca.' }}">
    <meta property="og:url"         content="{{ url()->current() }}">
    <meta property="og:image"       content="{{ asset('landing/images/og-image.jpg') }}">

    {{-- Canonical --}}
    <link rel="canonical" href="{{ url()->current() }}">

    {{-- Favicon --}}
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">

    {{-- Bootstrap 5 --}}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    {{-- Font Awesome --}}
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">

    {{-- Google Fonts --}}
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    {{-- Landing CSS --}}
    <link href="{{ asset('landing/css/landing.css') }}" rel="stylesheet">

    @stack('head')
</head>
<body>

    @include('components.landing.navbar')

    <main>
        @yield('content')
    </main>

    @include('components.landing.footer')

    {{-- Bootstrap 5 JS --}}
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    @stack('scripts')
</body>
</html>
