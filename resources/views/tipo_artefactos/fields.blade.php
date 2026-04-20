<!-- Nombre Field -->
<div class="form-group col-sm-6 required">
    <label for="nombre">Nombre:</label>
    <input type="text" name="nombre" id="nombre" class="form-control"
           value="{{ old('nombre', $tipo_artefacto->nombre ?? '') }}"
           placeholder="Ej: Lavadora, Refrigerador, Secadora..." required>
</div>

<!-- Descripcion Field -->
<div class="form-group col-sm-12">
    <label for="descripcion">Descripción:</label>
    <textarea name="descripcion" id="descripcion" class="form-control"
              rows="3" placeholder="Descripción opcional del tipo de artefacto...">{{ old('descripcion', $tipo_artefacto->descripcion ?? '') }}</textarea>
</div>
