<div class="row">
    <div class="col-md-4 form-group required">
        <label for="rut_empresa">RUT Empresa</label>
        <input type="text" name="rut_empresa" id="rut_empresa" class="form-control"
               value="{{ old('rut_empresa', $configuracion->rut_empresa ?? '') }}"
               placeholder="12345678-K" required>
        <small class="text-muted">Formato: 12345678-K</small>
    </div>
    <div class="col-md-4 form-group required">
        <label for="razon_social">Razon Social</label>
        <input type="text" name="razon_social" id="razon_social" class="form-control"
               value="{{ old('razon_social', $configuracion->razon_social ?? '') }}" required maxlength="100">
    </div>
    <div class="col-md-4 form-group required">
        <label for="giro">Giro</label>
        <input type="text" name="giro" id="giro" class="form-control"
               value="{{ old('giro', $configuracion->giro ?? '') }}" required maxlength="80">
        <small class="text-muted">Maximo 80 caracteres</small>
    </div>
</div>

<div class="row">
    <div class="col-md-3 form-group required">
        <label for="actividad_economica">Actividad Economica (CIIU)</label>
        <input type="number" name="actividad_economica" id="actividad_economica" class="form-control"
               value="{{ old('actividad_economica', $configuracion->actividad_economica ?? '') }}" required min="1">
    </div>
    <div class="col-md-5 form-group required">
        <label for="direccion">Direccion</label>
        <input type="text" name="direccion" id="direccion" class="form-control"
               value="{{ old('direccion', $configuracion->direccion ?? '') }}" required maxlength="70">
    </div>
    <div class="col-md-2 form-group required">
        <label for="comuna">Comuna</label>
        <input type="text" name="comuna" id="comuna" class="form-control"
               value="{{ old('comuna', $configuracion->comuna ?? '') }}" required maxlength="20">
    </div>
    <div class="col-md-2 form-group required">
        <label for="ciudad">Ciudad</label>
        <input type="text" name="ciudad" id="ciudad" class="form-control"
               value="{{ old('ciudad', $configuracion->ciudad ?? '') }}" required maxlength="20">
    </div>
</div>

<div class="row">
    <div class="col-md-3 form-group">
        <label for="telefono">Telefono</label>
        <input type="text" name="telefono" id="telefono" class="form-control"
               value="{{ old('telefono', $configuracion->telefono ?? '') }}" maxlength="20">
    </div>
    <div class="col-md-3 form-group">
        <label for="email_dte">Email DTE</label>
        <input type="email" name="email_dte" id="email_dte" class="form-control"
               value="{{ old('email_dte', $configuracion->email_dte ?? '') }}" maxlength="100">
    </div>
    <div class="col-md-2 form-group">
        <label for="sucursal_sii">Sucursal SII</label>
        <input type="number" name="sucursal_sii" id="sucursal_sii" class="form-control"
               value="{{ old('sucursal_sii', $configuracion->sucursal_sii ?? 0) }}" min="0">
        <small class="text-muted">0 = Casa Matriz</small>
    </div>
    <div class="col-md-2 form-group required">
        <label for="fecha_resolucion">Fecha Resolucion SII</label>
        <input type="date" name="fecha_resolucion" id="fecha_resolucion" class="form-control"
               value="{{ old('fecha_resolucion', isset($configuracion) && $configuracion->fecha_resolucion ? $configuracion->fecha_resolucion->format('Y-m-d') : '') }}" required>
    </div>
    <div class="col-md-2 form-group required">
        <label for="numero_resolucion">Nro. Resolucion</label>
        <input type="number" name="numero_resolucion" id="numero_resolucion" class="form-control"
               value="{{ old('numero_resolucion', $configuracion->numero_resolucion ?? 0) }}" required min="0">
        <small class="text-muted">0 = Certificacion</small>
    </div>
</div>

<div class="row">
    <div class="col-md-3 form-group required">
        <label for="ambiente">Ambiente</label>
        <select name="ambiente" id="ambiente" class="form-control" required>
            <option value="certificacion" {{ old('ambiente', $configuracion->ambiente ?? 'certificacion') === 'certificacion' ? 'selected' : '' }}>Certificacion (maullin)</option>
            <option value="produccion" {{ old('ambiente', $configuracion->ambiente ?? '') === 'produccion' ? 'selected' : '' }}>Produccion (palena)</option>
        </select>
    </div>
    @if(isset($configuracion) && $configuracion->exists)
    <div class="col-md-3 form-group">
        <label for="activa">Estado</label>
        <select name="activa" id="activa" class="form-control">
            <option value="0" {{ !old('activa', $configuracion->activa) ? 'selected' : '' }}>Inactiva</option>
            <option value="1" {{ old('activa', $configuracion->activa) ? 'selected' : '' }}>Activa</option>
        </select>
    </div>
    @endif
</div>
