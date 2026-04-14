@extends('layouts.app')

@section('content')
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Detalles del Cliente</h1>
                </div>
                <div class="col-sm-6 btn-group-md">
                    <a class="btn btn-default float-right" href="{{ route('clientes.index') }}">
                        Volver
                    </a>
                    @if (isset($cliente->coordenadas))
                        <a class="btn btn-default float-right mr-1" href="https://waze.com/ul?ll={{$cliente->coordenadas}}&navigate=yes"
                            target="_blank" 
                            class="btn btn-primary">
                            <i class="fab fa-waze"></i> Waze
                        </a>
                        <a href="https://www.google.com/maps/dir/?api=1&destination={{$cliente->coordenadas}}" 
                            target="_blank" 
                            class="btn btn-success float-right mr-1">
                                <i class="fas fa-map-marker-alt"></i> Google Maps
                        </a>
                    @endif
                </div>
            </div>
        </div>
    </section>

    <div class="content px-3">
        <div class="card">
            <div class="card-body">
                <div class="row">
                    <div class="col-sm-6">
                        <div class="form-group">
                            <label>Nombre:</label>
                            <p>{{ $cliente->nombre }}</p>
                        </div>
                    </div>

                    <div class="col-sm-6">
                        <div class="form-group">
                            <label>Apellido:</label>
                            <p>{{ $cliente->apellido }}</p>
                        </div>
                    </div>

                    <div class="col-sm-6">
                        <div class="form-group">
                            <label>Email:</label>
                            <p>{{ $cliente->email ?? 'No especificado' }}</p>
                        </div>
                    </div>

                    <div class="col-sm-6">
                        <div class="form-group">
                            <label>Teléfono:</label>
                            <p>{{ $cliente->numero_contacto }}</p>
                        </div>
                    </div>

                    <div class="col-sm-6">
                        <div class="form-group">
                            <label>Tipo Cliente:</label>
                            <p>
                                <span class="badge badge-{{ $cliente->tipo_cliente == 'empresa' ? 'primary' : ($cliente->tipo_cliente == 'concesion' ? 'success' : 'secondary') }}">
                                    {{ ucfirst($cliente->tipo_cliente) }}
                                </span>
                            </p>
                        </div>
                    </div>

                    <div class="col-sm-6">
                        <div class="form-group">
                            <label>RUT:</label>
                            <p>{{ $cliente->rut ?? 'No especificado' }}</p>
                        </div>
                    </div>

                    <div class="col-sm-12">
                        <div class="form-group">
                            <label>Dirección:</label>
                            <p>{{ $cliente->direccion }}</p>
                        </div>
                    </div>

                    <div class="col-sm-6">
                        <div class="form-group">
                            <label>Coordenadas:</label>
                            <p id="coordenadas-display">{{ $cliente->coordenadas ?? 'No especificadas' }}</p>
                            <div class="mt-1">
                                <button type="button" class="btn btn-sm btn-outline-secondary" id="btnObtenerUbicacionShow">
                                    <i class="fas fa-map-marker-alt"></i> Obtener ubicación actual
                                </button>
                                <button type="button" class="btn btn-sm btn-success d-none" id="btnGuardarUbicacion">
                                    <i class="fas fa-save"></i> Guardar ubicación
                                </button>
                                <small id="nuevas-coordenadas-preview" class="text-muted d-block mt-1"></small>
                            </div>
                        </div>
                    </div>

                    <div class="col-sm-6">
                        <div class="form-group">
                            <label>Estado:</label>
                            <p>
                                <span class="badge badge-{{ $cliente->estado ? 'success' : 'danger' }}">
                                    {{ $cliente->estado ? 'Activo' : 'Inactivo' }}
                                </span>
                            </p>
                        </div>
                    </div>

                    @if($cliente->nota)
                    <div class="col-sm-12">
                        <div class="form-group">
                            <label>Nota:</label>
                            <p>{{ $cliente->nota }}</p>
                        </div>
                    </div>
                    @endif
                </div>
            </div>

            <div class="card-footer">
                <a href="{{ route('clientes.edit', $cliente->id) }}" class="btn btn-primary">Editar</a>
                <a href="{{ route('clientes.index') }}" class="btn btn-default">Volver</a>
            </div>
        </div>
    </div>
@push('page_scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const btnObtener = document.getElementById('btnObtenerUbicacionShow');
    const btnGuardar = document.getElementById('btnGuardarUbicacion');
    const preview   = document.getElementById('nuevas-coordenadas-preview');
    const display   = document.getElementById('coordenadas-display');
    let coordenadasObtenidas = null;

    // ── Notify helper (usa Bootstrap toast de AdminLTE) ──────────────────
    function notify(message, type) {
        type = type || 'success';
        const colors = { success: '#28a745', error: '#dc3545', warning: '#ffc107', info: '#17a2b8' };
        const icons  = { success: 'fas fa-check-circle', error: 'fas fa-times-circle', warning: 'fas fa-exclamation-circle', info: 'fas fa-info-circle' };

        const id = 'notify-' + Date.now();
        const toast = $(`
            <div id="${id}" style="
                position:fixed; bottom:20px; right:20px; z-index:9999;
                min-width:280px; max-width:360px;
                background:#fff; border-left:4px solid ${colors[type]};
                border-radius:4px; padding:12px 16px;
                box-shadow:0 4px 12px rgba(0,0,0,.15);
                display:flex; align-items:center; gap:10px;
                animation: slideIn .3s ease;">
                <i class="${icons[type]}" style="color:${colors[type]}; font-size:1.2rem;"></i>
                <span style="flex:1; font-size:.9rem;">${message}</span>
                <button type="button" style="background:none;border:none;cursor:pointer;font-size:1rem;color:#aaa;" onclick="$('#${id}').remove()">×</button>
            </div>
        `);

        $('body').append(toast);
        setTimeout(function () { $('#' + id).fadeOut(400, function () { $(this).remove(); }); }, 4000);
    }

    // ── Obtener ubicación ─────────────────────────────────────────────────
    btnObtener.addEventListener('click', function () {
        if (!navigator.geolocation) {
            notify('La geolocalización no es compatible con este navegador.', 'error');
            return;
        }

        btnObtener.disabled = true;
        btnObtener.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Obteniendo...';

        navigator.geolocation.getCurrentPosition(
            function (position) {
                coordenadasObtenidas = position.coords.latitude + ',' + position.coords.longitude;
                preview.textContent  = 'Nueva ubicación: ' + coordenadasObtenidas;

                btnGuardar.classList.remove('d-none');
                btnObtener.disabled = false;
                btnObtener.innerHTML = '<i class="fas fa-map-marker-alt"></i> Obtener ubicación actual';

                notify('Ubicación obtenida. Presiona "Guardar ubicación" para asociarla.', 'info');
            },
            function (error) {
                const msgs = {
                    [error.PERMISSION_DENIED]:    'Permisos de ubicación denegados.',
                    [error.POSITION_UNAVAILABLE]: 'Ubicación no disponible.',
                    [error.TIMEOUT]:              'Tiempo de espera agotado.',
                };
                notify('Error: ' + (msgs[error.code] || 'Error desconocido.'), 'error');

                btnObtener.disabled = false;
                btnObtener.innerHTML = '<i class="fas fa-map-marker-alt"></i> Obtener ubicación actual';
            },
            { enableHighAccuracy: true, timeout: 10000, maximumAge: 60000 }
        );
    });

    // ── Guardar ubicación via AJAX ────────────────────────────────────────
    btnGuardar.addEventListener('click', function () {
        if (!coordenadasObtenidas) return;

        btnGuardar.disabled = true;
        btnGuardar.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Guardando...';

        $.ajax({
            url: '{{ route("clientes.updateCoordenadas", $cliente->id) }}',
            method: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                coordenadas: coordenadasObtenidas
            },
            success: function (response) {
                if (response.success) {
                    display.textContent = response.coordenadas;
                    preview.textContent = '';
                    coordenadasObtenidas = null;

                    btnGuardar.classList.add('d-none');
                    btnGuardar.disabled = false;
                    btnGuardar.innerHTML = '<i class="fas fa-save"></i> Guardar ubicación';

                    notify('Ubicación asociada correctamente.', 'success');
                }
            },
            error: function () {
                notify('Error al guardar la ubicación. Intenta nuevamente.', 'error');
                btnGuardar.disabled = false;
                btnGuardar.innerHTML = '<i class="fas fa-save"></i> Guardar ubicación';
            }
        });
    });
});
</script>
@endpush

@endsection