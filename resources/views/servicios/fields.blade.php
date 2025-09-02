<!-- Nombre Servicio Field -->
<div class="form-group col-sm-6 required">
    <label for="nombre_servicio">Nombre del Servicio:</label>
    <input type="text" name="nombre_servicio" id="nombre_servicio" class="form-control" value="{{ old('nombre_servicio', $servicio->nombre_servicio ?? '') }}" required>
</div>

<!-- Precio Field -->
<div class="form-group col-sm-6 required">
    <label for="precio">Precio:</label>
    <input type="number" name="precio" id="precio" class="form-control" step="0.01" min="0" value="{{ old('precio', $servicio->precio ?? '') }}" required>
</div>

<!-- Duracion Estimada Field -->
<div class="form-group col-sm-6">
    <label for="duracion_estimada">Duración Estimada (minutos):</label>
    <input type="number" name="duracion_estimada" id="duracion_estimada" class="form-control" min="1" value="{{ old('duracion_estimada', $servicio->duracion_estimada ?? '') }}">
</div>

<!-- Estado Field -->
<div class="form-group col-sm-6">
    <label for="estado">Estado:</label>
    <div class="form-check">
        <input type="hidden" name="estado" value="0">
        <input type="checkbox" name="estado" id="estado" class="form-check-input" value="1" {{ old('estado', $servicio->estado ?? true) ? 'checked' : '' }}>
        <label class="form-check-label" for="estado">Activo</label>
    </div>
</div>

<!-- Requiere Repuestos Field -->
<div class="form-group col-sm-6">
    <label for="requiere_repuestos">Requiere Repuestos:</label>
    <div class="form-check">
        <input type="hidden" name="requiere_repuestos" value="0">
        <input type="checkbox" name="requiere_repuestos" id="requiere_repuestos" class="form-check-input" value="1" {{ old('requiere_repuestos', $servicio->requiere_repuestos ?? false) ? 'checked' : '' }}>
        <label class="form-check-label" for="requiere_repuestos">Sí, requiere repuestos</label>
    </div>
</div>

<!-- Descripcion Field -->
<div class="form-group col-sm-12">
    <label for="descripcion">Descripción:</label>
    <textarea name="descripcion" id="descripcion" class="form-control" rows="3">{{ old('descripcion', $servicio->descripcion ?? '') }}</textarea>
</div>