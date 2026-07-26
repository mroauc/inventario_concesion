<!-- Nombre Field -->
<div class="form-group col-sm-8">
    <label for="nombre">Nombre del artefacto:</label>
    <input type="text" name="nombre" id="nombre" class="form-control"
           value="{{ old('nombre', $oferta->nombre ?? '') }}"
           placeholder="Ej: Lavadora Samsung 10 Kg carga frontal" required>
</div>

<!-- Precio Field -->
<div class="form-group col-sm-4">
    <label for="precio">Precio (CLP):</label>
    <div class="input-group">
        <div class="input-group-prepend"><span class="input-group-text">$</span></div>
        <input type="number" name="precio" id="precio" class="form-control"
               value="{{ old('precio', isset($oferta) ? (int) $oferta->precio : '') }}"
               min="0" step="1" placeholder="199990" required>
    </div>
</div>

<!-- Descripcion Field -->
<div class="form-group col-sm-12">
    <label for="descripcion">Descripción:</label>
    <textarea name="descripcion" id="descripcion" class="form-control" rows="4"
              placeholder="Características, estado, motivo del precio rebajado...">{{ old('descripcion', $oferta->descripcion ?? '') }}</textarea>
</div>

<!-- Fotos Field -->
@php $fotosActuales = $oferta->fotos ?? []; @endphp

<div class="form-group col-sm-12">
    <label class="d-block">Fotografías (máximo 3):</label>

    {{-- Una casilla por posición: cada una es su propio input y se reemplaza sola --}}
    <div class="foto-slots">
        @for($i = 0; $i < 3; $i++)
            @php $actual = $fotosActuales[$i] ?? null; @endphp
            <div class="foto-slot {{ $actual ? 'foto-slot--llena' : '' }}" data-slot="{{ $i }}">
                <input type="file" name="fotos[{{ $i }}]" id="foto{{ $i }}"
                       class="foto-slot__input" accept="image/*">

                {{-- Si se borra una foto existente, el backend lo sabe por este flag --}}
                <input type="hidden" name="fotos_eliminadas[{{ $i }}]" value="0"
                       class="foto-slot__eliminar">

                <label for="foto{{ $i }}" class="foto-slot__label">
                    <img src="{{ $actual ? asset('storage/' . $actual) : '' }}"
                         alt="" class="foto-slot__img" {{ $actual ? '' : 'hidden' }}>

                    <span class="foto-slot__vacio" {{ $actual ? 'hidden' : '' }}>
                        <i class="fas fa-plus"></i>
                        <span class="foto-slot__texto">Agregar foto</span>
                    </span>

                    <span class="foto-slot__num">{{ $i + 1 }}</span>
                </label>

                <button type="button" class="foto-slot__quitar" {{ $actual ? '' : 'hidden' }}
                        title="Quitar esta foto" aria-label="Quitar foto {{ $i + 1 }}">&times;</button>

                <small class="foto-slot__nombre text-muted d-block text-truncate"></small>
            </div>
        @endfor
    </div>

    <small class="form-text text-muted">
        JPG, PNG o WEBP. Máximo 5 MB cada una. Se redimensionan automáticamente.
        Haz clic en una casilla para elegir o cambiar esa foto.
    </small>
    <div id="preview-aviso" class="text-danger small mt-2 d-none"></div>
</div>


<!-- Vendido Field -->
<div class="form-group col-sm-6">
    <label>Estado de venta:</label>
    <div class="form-check mt-2">
        <input type="hidden" name="vendido" value="0">
        <input type="checkbox" name="vendido" id="vendido" class="form-check-input" value="1"
               {{ old('vendido', $oferta->vendido ?? false) ? 'checked' : '' }}>
        <label class="form-check-label" for="vendido">Vendido</label>
    </div>
    <small class="form-text text-muted">Se mostrará en gris con etiqueta "Vendido" en la landing.</small>
</div>

<!-- Estado Field -->
<div class="form-group col-sm-6">
    <label>Publicación:</label>
    <div class="form-check mt-2">
        <input type="hidden" name="estado" value="0">
        <input type="checkbox" name="estado" id="estado" class="form-check-input" value="1"
               {{ old('estado', $oferta->estado ?? true) ? 'checked' : '' }}>
        <label class="form-check-label" for="estado">Visible en la landing</label>
    </div>
    <small class="form-text text-muted">Desmarcar la oculta por completo del sitio público.</small>
</div>
