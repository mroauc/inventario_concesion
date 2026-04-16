<!-- Nombre Field -->
<div class="form-group col-sm-6 required">
    <label for="nombre">Nombre:</label>
    <input type="text" name="nombre" id="nombre" class="form-control" value="{{ old('nombre', $cliente->nombre ?? '') }}" required>
</div>

<!-- Apellido Field -->
<div class="form-group col-sm-6 required">
    <label for="apellido">Apellido:</label>
    <input type="text" name="apellido" id="apellido" class="form-control" value="{{ old('apellido', $cliente->apellido ?? '') }}" required>
</div>

<!-- Email Field -->
<div class="form-group col-sm-6">
    <label for="email">Email:</label>
    <input type="email" name="email" id="email" class="form-control" value="{{ old('email', $cliente->email ?? '') }}">
</div>

<!-- Numero Contacto Field -->
<div class="form-group col-sm-6 required">
    <label for="numero_contacto">Teléfono:</label>
    <input type="text" name="numero_contacto" id="numero_contacto" class="form-control" value="{{ old('numero_contacto', $cliente->numero_contacto ?? '') }}" required>
</div>

<!-- Tipo Cliente Field -->
<div class="form-group col-sm-6 required">
    <label for="tipo_cliente">Tipo Cliente:</label>
    <select name="tipo_cliente" id="tipo_cliente" class="form-control" required>
        <option value="">Seleccionar tipo</option>
        <option value="residencial" {{ old('tipo_cliente', $cliente->tipo_cliente ?? '') == 'residencial' ? 'selected' : '' }}>Residencial</option>
        <option value="empresa" {{ old('tipo_cliente', $cliente->tipo_cliente ?? '') == 'empresa' ? 'selected' : '' }}>Empresa</option>
        <option value="concesion" {{ old('tipo_cliente', $cliente->tipo_cliente ?? '') == 'concesion' ? 'selected' : '' }}>Concesión</option>
    </select>
</div>

<!-- RUT Field -->
<div class="form-group col-sm-6">
    <label for="rut">RUT:</label>
    <input type="text" name="rut" id="rut" class="form-control" value="{{ old('rut', $cliente->rut ?? '') }}" autocomplete="off">
    <small id="rut-feedback" class="form-text" style="display:none;"></small>
</div>

<!-- Direccion Field -->
<div class="form-group col-sm-8 required">
    <label for="direccion">Dirección:</label>
    <input type="text" name="direccion" id="direccion" class="form-control" value="{{ old('direccion', $cliente->direccion ?? '') }}" required>
</div>

<!-- Ciudad Field -->
<div class="form-group col-sm-4">
    <label for="ciudad">Ciudad:</label>
    <input type="text" name="ciudad" id="ciudad" class="form-control" value="{{ old('ciudad', $cliente->ciudad ?? '') }}">
</div>

<!-- Coordenadas Field -->
<div class="form-group col-sm-6">
    <label for="coordenadas">Coordenadas:</label>
    <input type="text" name="coordenadas" id="coordenadas" class="form-control" value="{{ old('coordenadas', $cliente->coordenadas ?? '') }}" placeholder="Ej: -33.4489, -70.6693">
    <div class="input-group">
        <div class="input-group-append">
            <button type="button" class="btn btn-outline-secondary" id="btnObtenerUbicacion">
                <i class="fas fa-map-marker-alt"></i> Obtener ubicación
            </button>
            <button type="button" class="btn btn-outline-secondary" id="btnLimpiarUbicacion">
                 Limpiar
            </button>
        </div>
    </div>
</div>

<!-- Estado Field -->
<div class="form-group col-sm-6">
    <label for="estado">Estado:</label>
    <div class="form-check">
        <input type="hidden" name="estado" value="0">
        <input type="checkbox" name="estado" id="estado" class="form-check-input" value="1" {{ old('estado', $cliente->estado ?? true) ? 'checked' : '' }}>
        <label class="form-check-label" for="estado">Activo</label>
    </div>
</div>

<!-- Nota Field -->
<div class="form-group col-sm-12">
    <label for="nota">Nota:</label>
    <textarea name="nota" id="nota" class="form-control" rows="3">{{ old('nota', $cliente->nota ?? '') }}</textarea>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const rutInput = document.getElementById('rut');
    const rutFeedback = document.getElementById('rut-feedback');

    function formatearRut(valor) {
        let limpio = valor.replace(/[^0-9kK]/g, '').toUpperCase();
        if (limpio.length === 0) return '';
        let cuerpo = limpio.slice(0, -1);
        let dv = limpio.slice(-1);
        if (cuerpo.length === 0) return dv;
        return cuerpo + '-' + dv;
    }

    function validarRut(rut) {
        // Esperar al menos cuerpo + guion + dv (ej: "1-9")
        if (!rut || rut.length < 3) return null;
        let limpio = rut.replace(/[^0-9kK]/g, '').toUpperCase();
        if (limpio.length < 2) return null;
        let cuerpo = limpio.slice(0, -1);
        let dvIngresado = limpio.slice(-1);
        if (!/^\d+$/.test(cuerpo)) return false;
        let suma = 0;
        let multiplo = 2;
        for (let i = cuerpo.length - 1; i >= 0; i--) {
            suma += parseInt(cuerpo[i]) * multiplo;
            multiplo = multiplo === 7 ? 2 : multiplo + 1;
        }
        let dvEsperado = 11 - (suma % 11);
        let dvCalculado = dvEsperado === 11 ? '0' : dvEsperado === 10 ? 'K' : String(dvEsperado);
        return dvIngresado === dvCalculado;
    }

    function actualizarEstadoRut() {
        const valor = rutInput.value;
        if (valor === '') {
            rutInput.classList.remove('is-valid', 'is-invalid');
            rutFeedback.style.display = 'none';
            return;
        }
        const resultado = validarRut(valor);
        if (resultado === null) {
            rutInput.classList.remove('is-valid', 'is-invalid');
            rutFeedback.style.display = 'none';
        } else if (resultado === true) {
            rutInput.classList.remove('is-invalid');
            rutInput.classList.add('is-valid');
            rutFeedback.style.display = 'none';
        } else {
            rutInput.classList.remove('is-valid');
            rutInput.classList.add('is-invalid');
            rutFeedback.style.display = 'block';
            rutFeedback.style.color = '#dc3545';
            rutFeedback.textContent = 'RUT inválido';
        }
    }

    rutInput.addEventListener('input', function() {
        const pos = this.selectionStart;
        const valorAntes = this.value;
        this.value = formatearRut(this.value);
        const diff = this.value.length - valorAntes.length;
        this.setSelectionRange(pos + diff, pos + diff);
        actualizarEstadoRut();
    });

    // Formatear y validar el valor inicial si viene precargado (edición)
    if (rutInput.value !== '') {
        rutInput.value = formatearRut(rutInput.value);
    }
    actualizarEstadoRut();

    const btnLimpiarUbicacion = document.getElementById('btnLimpiarUbicacion');
    const btnObtenerUbicacion = document.getElementById('btnObtenerUbicacion');
    const coordenadasInput = document.getElementById('coordenadas');

    btnLimpiarUbicacion.addEventListener('click', function() {
        coordenadasInput.value = '';
    });

    btnObtenerUbicacion.addEventListener('click', function() {
        if (navigator.geolocation) {
            btnObtenerUbicacion.disabled = true;
            btnObtenerUbicacion.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Obteniendo...';
            
            navigator.geolocation.getCurrentPosition(
                function(position) {
                    const lat = position.coords.latitude;
                    const lng = position.coords.longitude;
                    coordenadasInput.value = lat + ',' + lng;
                    
                    btnObtenerUbicacion.disabled = false;
                    btnObtenerUbicacion.innerHTML = '<i class="fas fa-map-marker-alt"></i> Obtener ubicación';
                },
                function(error) {
                    let mensaje = 'Error al obtener la ubicación: ';
                    switch(error.code) {
                        case error.PERMISSION_DENIED:
                            mensaje += 'Permisos de ubicación denegados.';
                            break;
                        case error.POSITION_UNAVAILABLE:
                            mensaje += 'Ubicación no disponible.';
                            break;
                        case error.TIMEOUT:
                            mensaje += 'Tiempo de espera agotado.';
                            break;
                        default:
                            mensaje += 'Error desconocido.';
                            break;
                    }
                    alert(mensaje);
                    
                    btnObtenerUbicacion.disabled = false;
                    btnObtenerUbicacion.innerHTML = '<i class="fas fa-map-marker-alt"></i> Obtener ubicación';
                },
                {
                    enableHighAccuracy: true,
                    timeout: 10000,
                    maximumAge: 60000
                }
            );
        } else {
            alert('La geolocalización no es compatible con este navegador.');
        }
    });
});
</script>