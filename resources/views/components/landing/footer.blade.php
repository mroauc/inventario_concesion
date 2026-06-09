<footer class="landing-footer">
    <div class="container">
        <div class="row gy-4">

            {{-- Columna: marca --}}
            <div class="col-lg-4">
                <div class="d-flex align-items-center gap-2 mb-3">
                    <i class="fas fa-wrench fs-5 text-brand-light"></i>
                    <span class="fw-bold fs-5">ROAVAL <span class="fw-light">LIMITADA</span></span>
                </div>
                <p class="text-footer-muted small mb-3">
                    Servicio técnico autorizado de línea blanca en la Provincia de Linares
                    y Cauquenes, Región del Maule.
                </p>
                <div class="d-flex gap-2">
                    <a href="https://wa.me/56933223194" target="_blank" rel="noopener"
                       class="btn btn-whatsapp btn-sm" title="WhatsApp">
                        <i class="fab fa-whatsapp"></i>
                    </a>
                    <a href="https://www.instagram.com/roaval_serviciotecnico" target="_blank" rel="noopener"
                       class="btn btn-instagram btn-sm" title="Instagram">
                        <i class="fab fa-instagram"></i>
                    </a>
                </div>
            </div>

            {{-- Columna: links --}}
            <div class="col-sm-6 col-lg-2 offset-lg-1">
                <h6 class="footer-heading">Navegación</h6>
                <ul class="list-unstyled footer-links">
                    <li><a href="{{ route('landing.home') }}">Inicio</a></li>
                    <li><a href="{{ route('landing.repuestos') }}">Repuestos / Accesorios</a></li>
                    <li><a href="{{ route('landing.conocenos') }}">Conócenos</a></li>
                    <li><a href="{{ route('landing.contacto') }}">Contacto</a></li>
                </ul>
            </div>

            {{-- Columna: contacto --}}
            <div class="col-sm-6 col-lg-3">
                <h6 class="footer-heading">Contacto</h6>
                <ul class="list-unstyled footer-contact small">
                    <li>
                        <i class="fas fa-map-marker-alt me-2 text-brand-light"></i>
                        Lautaro Nº 533, Linares, Región del Maule
                    </li>
                    <li>
                        <i class="fas fa-phone me-2 text-brand-light"></i>
                        <a href="tel:+5673263342">(73) 2633420</a>
                    </li>
                    <li>
                        <i class="fas fa-envelope me-2 text-brand-light"></i>
                        <a href="mailto:contactosai@roaval.com">contactosai@roaval.com</a>
                    </li>
                    <li>
                        <i class="fas fa-clock me-2 text-brand-light"></i>
                        Lun–Jue 9:00–18:30 · Vie 9:00–17:30
                    </li>
                    <li class="ps-4">Sáb 9:15–13:00 · Dom Cerrado</li>
                </ul>
            </div>

            {{-- Columna: marcas --}}
            <div class="col-lg-2">
                <h6 class="footer-heading">Servicio Autorizado</h6>
                <div class="d-flex flex-wrap gap-2">
                    @foreach(['SAI', 'Electrolux', 'Mademsa', 'Fensa'] as $brand)
                    <span class="badge-brand">{{ $brand }}</span>
                    @endforeach
                </div>
            </div>

        </div>

        <hr class="footer-divider my-4">

        <div class="row align-items-center">
            <div class="col-md-6 text-center text-md-start">
                <small class="text-footer-muted">
                    © {{ date('Y') }} ROAVAL LIMITADA. Todos los derechos reservados.
                </small>
            </div>
            <div class="col-md-6 text-center text-md-end mt-2 mt-md-0">
                <small class="text-footer-muted">
                    Linares · Parral · Longaví · Colbún · Cauquenes
                </small>
            </div>
        </div>
    </div>
</footer>
