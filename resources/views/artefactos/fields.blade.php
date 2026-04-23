<!-- Tipo Artefacto Field -->
<div class="form-group col-sm-6">
    <label for="tipo_artefacto_id">Tipo de Artefacto:</label>
    <select name="tipo_artefacto_id" id="tipo_artefacto_id" class="form-control select2">
        <option value="">Sin tipo asignado</option>
        @foreach($tipos as $tipo)
            <option value="{{ $tipo->id }}"
                {{ old('tipo_artefacto_id', $artefacto->tipo_artefacto_id ?? '') == $tipo->id ? 'selected' : '' }}>
                {{ $tipo->nombre }}
            </option>
        @endforeach
    </select>
</div>

<!-- Estado Field -->
<div class="form-group col-sm-6">
    <label for="estado">Estado:</label>
    <div class="form-check mt-2">
        <input type="hidden" name="estado" value="0">
        <input type="checkbox" name="estado" id="estado" class="form-check-input" value="1"
               {{ old('estado', $artefacto->estado ?? true) ? 'checked' : '' }}>
        <label class="form-check-label" for="estado">Activo</label>
    </div>
</div>

<!-- Codigo Field -->
<div class="form-group col-sm-6">
    <label for="codigo">Código:</label>
    <input type="text" name="codigo" id="codigo" class="form-control"
           value="{{ old('codigo', $artefacto->codigo ?? '') }}"
           placeholder="Ej: ELX-PC16ZS">
</div>

<!-- Marca Field -->
<div class="form-group col-sm-6">
    <label for="marca">Marca:</label>
    <select name="marca" id="marca" class="form-control select2">
        <option value="">Sin marca asignada</option>
        @foreach(['Electrolux', 'Fensa', 'Mademsa', 'Somela'] as $marca)
            <option value="{{ $marca }}"
                {{ old('marca', $artefacto->marca ?? '') == $marca ? 'selected' : '' }}>
                {{ $marca }}
            </option>
        @endforeach
    </select>
</div>

<!-- Modelo Field -->
<div class="form-group col-sm-6">
    <label for="modelo">Modelo:</label>
    <input type="text" name="modelo" id="modelo" class="form-control"
           value="{{ old('modelo', $artefacto->modelo ?? '') }}"
           placeholder="Ej: Premium Care 16ZS">
</div>

<!-- Descripcion Field -->
<div class="form-group col-sm-12">
    <label for="descripcion">Descripción:</label>
    <textarea name="descripcion" id="descripcion" class="form-control"
              rows="3" placeholder="Ej: Superior 16 Kg">{{ old('descripcion', $artefacto->descripcion ?? '') }}</textarea>
</div>
