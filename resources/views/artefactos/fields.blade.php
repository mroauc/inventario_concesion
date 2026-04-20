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

<!-- Marca Field -->
<div class="form-group col-sm-6">
    <label for="marca">Marca:</label>
    <input type="text" name="marca" id="marca" class="form-control"
           value="{{ old('marca', $artefacto->marca ?? '') }}"
           placeholder="Ej: Samsung">
</div>

<!-- Modelo Field -->
<div class="form-group col-sm-6">
    <label for="modelo">Modelo:</label>
    <input type="text" name="modelo" id="modelo" class="form-control"
           value="{{ old('modelo', $artefacto->modelo ?? '') }}"
           placeholder="Ej: WF16T6500GW">
</div>

<!-- Descripcion Field -->
<div class="form-group col-sm-12">
    <label for="descripcion">Descripción:</label>
    <textarea name="descripcion" id="descripcion" class="form-control"
              rows="3" placeholder="Ej: Carga frontal 10 kg, clase energética A+++">{{ old('descripcion', $artefacto->descripcion ?? '') }}</textarea>
</div>
