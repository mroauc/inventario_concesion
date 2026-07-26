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
<div class="form-group col-sm-12">
    <label for="fotos">Fotografías (máximo 3):</label>
    <div class="custom-file">
        <input type="file" name="fotos[]" id="fotos" class="custom-file-input"
               accept="image/*" multiple>
        <label class="custom-file-label" for="fotos">Seleccionar imágenes...</label>
    </div>
    <small class="form-text text-muted">
        JPG, PNG o WEBP. Máximo 5 MB cada una. Se redimensionan automáticamente.
        @isset($oferta)
            Si subes archivos nuevos, <strong>reemplazarán todas las fotos actuales</strong>.
        @endisset
    </small>
</div>

@isset($oferta)
    @if($oferta->fotos)
        <div class="form-group col-sm-12">
            <label class="d-block">Fotos actuales:</label>
            <div class="d-flex flex-wrap" style="gap:.5rem;">
                @foreach($oferta->fotos as $foto)
                    <img src="{{ asset('storage/' . $foto) }}" alt=""
                         style="width:120px;height:120px;object-fit:cover;border-radius:6px;border:1px solid #dee2e6;">
                @endforeach
            </div>
        </div>
    @endif
@endisset

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
