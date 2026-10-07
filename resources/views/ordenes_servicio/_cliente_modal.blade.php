<div class="modal fade" id="modal-nuevo-cliente" tabindex="-1" role="dialog" aria-labelledby="modal-nuevo-cliente-titulo" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
        <div class="modal-content">
            <form id="form-nuevo-cliente" novalidate>
                @csrf
                <div class="modal-header border-bottom-0 pb-0">
                    <div>
                        <h4 class="modal-title font-weight-bold" id="modal-nuevo-cliente-titulo">
                            <i class="fas fa-user-plus text-brand mr-2"></i>Crear nuevo cliente
                        </h4>
                        <p class="text-muted mb-0 mt-1">Completa los datos esenciales. El cliente quedará seleccionado en la orden.</p>
                    </div>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body pt-3">
                    <div class="alert alert-danger d-none" id="nuevo-cliente-error" role="alert" aria-live="assertive"></div>

                    <div class="row">
                        <div class="form-group col-md-6">
                            <label for="nuevo-cliente-nombre">Nombre <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="nuevo-cliente-nombre" name="nombre" maxlength="255" autocomplete="given-name" required>
                            <div class="invalid-feedback"></div>
                        </div>
                        <div class="form-group col-md-6">
                            <label for="nuevo-cliente-apellido">Apellido <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="nuevo-cliente-apellido" name="apellido" maxlength="255" autocomplete="family-name" required>
                            <div class="invalid-feedback"></div>
                        </div>
                        <div class="form-group col-md-6">
                            <label for="nuevo-cliente-telefono">Teléfono <span class="text-danger">*</span></label>
                            <input type="tel" class="form-control" id="nuevo-cliente-telefono" name="numero_contacto" maxlength="255" autocomplete="tel" placeholder="Ej: +56 9 1234 5678" required>
                            <div class="invalid-feedback"></div>
                        </div>
                        <div class="form-group col-md-6">
                            <label for="nuevo-cliente-tipo">Tipo de cliente <span class="text-danger">*</span></label>
                            <select class="form-control" id="nuevo-cliente-tipo" name="tipo_cliente" required>
                                <option value="residencial" selected>Residencial</option>
                                <option value="empresa">Empresa</option>
                                <option value="concesion">Concesión</option>
                            </select>
                            <div class="invalid-feedback"></div>
                        </div>
                        <div class="form-group col-md-8 mb-0">
                            <label for="nuevo-cliente-direccion">Dirección <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="nuevo-cliente-direccion" name="direccion" maxlength="255" autocomplete="street-address" required>
                            <div class="invalid-feedback"></div>
                        </div>
                        <div class="form-group col-md-4 mb-0">
                            <label for="nuevo-cliente-ciudad">Ciudad</label>
                            <input type="text" class="form-control" id="nuevo-cliente-ciudad" name="ciudad" maxlength="255" autocomplete="address-level2">
                            <div class="invalid-feedback"></div>
                        </div>
                        <div class="form-group col-md-6 mt-3 mb-0">
                            <label for="nuevo-cliente-rut">RUT</label>
                            <input type="text" class="form-control" id="nuevo-cliente-rut" name="rut" maxlength="255" autocomplete="off" placeholder="12.345.678-9">
                            <div class="invalid-feedback"></div>
                        </div>
                    </div>

                    <button class="btn btn-link btn-sm px-0 mt-3" type="button" data-toggle="collapse" data-target="#nuevo-cliente-opcionales" aria-expanded="false" aria-controls="nuevo-cliente-opcionales">
                        <i class="fas fa-plus-circle mr-1"></i>Agregar correo o nota (opcional)
                    </button>
                    <div class="collapse" id="nuevo-cliente-opcionales">
                        <div class="row pt-2">
                            <div class="form-group col-12">
                                <label for="nuevo-cliente-email">Correo electrónico</label>
                                <input type="email" class="form-control" id="nuevo-cliente-email" name="email" maxlength="255" autocomplete="email" placeholder="cliente@correo.cl">
                                <div class="invalid-feedback"></div>
                            </div>
                            <div class="form-group col-12 mb-0">
                                <label for="nuevo-cliente-nota">Nota</label>
                                <textarea class="form-control" id="nuevo-cliente-nota" name="nota" rows="2" placeholder="Información útil para futuras atenciones"></textarea>
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>
                    </div>
                    <small class="text-muted d-block mt-3"><span class="text-danger">*</span> Campos obligatorios</small>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-outline-secondary" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-brand" id="btn-guardar-cliente">
                        <i class="fas fa-save mr-1"></i><span>Crear y seleccionar</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
