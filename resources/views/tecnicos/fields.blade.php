<!-- User Field -->
<div class="form-group col-sm-6">
    <label for="user_id">Usuario:</label>
    <select name="user_id" id="user_id" class="form-control">
        <option value="">Sin asignar</option>
        @foreach($users as $user)
            <option value="{{ $user->id }}" {{ old('user_id', $tecnico->user_id ?? '') == $user->id ? 'selected' : '' }}>
                {{ $user->name }}
            </option>
        @endforeach
    </select>
</div>

<!-- Nombre Field -->
<div class="form-group col-sm-6 required">
    <label for="especialidad">Nombre:</label>
    <input type="text" name="nombre" id="nombre" class="form-control" value="{{ old('nombre', $tecnico->nombre ?? '') }}" required>
</div>

<!-- Especialidad Field -->
<div class="form-group col-sm-6 required">
    <label for="especialidad">Especialidad:</label>
    <input type="text" name="especialidad" id="especialidad" class="form-control" value="{{ old('especialidad', $tecnico->especialidad ?? '') }}" required>
</div>

<!-- Telefono Contacto Field -->
<div class="form-group col-sm-6">
    <label for="telefono_contacto">Teléfono de Contacto:</label>
    <input type="text" name="telefono_contacto" id="telefono_contacto" class="form-control" value="{{ old('telefono_contacto', $tecnico->telefono_contacto ?? '') }}">
</div>

<!-- Email Contacto Field -->
<div class="form-group col-sm-6">
    <label for="email_contacto">Email de Contacto:</label>
    <input type="email" name="email_contacto" id="email_contacto" class="form-control" value="{{ old('email_contacto', $tecnico->email_contacto ?? '') }}">
</div>

<!-- Zona Cobertura Field -->
<div class="form-group col-sm-6">
    <label for="zona_cobertura">Zona de Cobertura:</label>
    <input type="text" name="zona_cobertura" id="zona_cobertura" class="form-control" value="{{ old('zona_cobertura', $tecnico->zona_cobertura ?? '') }}">
</div>

<!-- Disponibilidad Field -->
<div class="form-group col-sm-6 required">
    <label for="disponibilidad">Disponibilidad:</label>
    <select name="disponibilidad" id="disponibilidad" class="form-control" required>
        <option value="disponible" {{ old('disponibilidad', $tecnico->disponibilidad ?? 'disponible') == 'disponible' ? 'selected' : '' }}>Disponible</option>
        <option value="ocupado" {{ old('disponibilidad', $tecnico->disponibilidad ?? '') == 'ocupado' ? 'selected' : '' }}>Ocupado</option>
        <option value="de_baja" {{ old('disponibilidad', $tecnico->disponibilidad ?? '') == 'de_baja' ? 'selected' : '' }}>De Baja</option>
    </select>
</div>

<!-- Certificaciones Field -->
<div class="form-group col-sm-12">
    <label for="certificaciones">Certificaciones:</label>
    <textarea name="certificaciones" id="certificaciones" class="form-control" rows="3">{{ old('certificaciones', $tecnico->certificaciones ?? '') }}</textarea>
</div>

<!-- Nota Field -->
<div class="form-group col-sm-12">
    <label for="nota">Nota:</label>
    <textarea name="nota" id="nota" class="form-control" rows="3">{{ old('nota', $tecnico->nota ?? '') }}</textarea>
</div>