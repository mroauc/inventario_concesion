@extends('layouts.app')

@section('content')
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-12">
                    <h1>Crear Orden de Servicio</h1>
                </div>
            </div>
        </div>
    </section>

    <div class="content px-3">
        @include('flash-message')

        <form method="POST" action="{{ route('ordenes_servicio.store') }}">
            @csrf
            <div class="row">
                <div class="col-md-8">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Información de la Orden</h3>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="numero">Número de Orden</label>
                                        <input type="text" class="form-control"
                                               value="#{{ $proximoNumero }}"
                                               readonly disabled>
                                        <small class="form-text text-muted">Asignado automáticamente al guardar.</small>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="folio_garantia">Folio Garantía</label>
                                        <input type="text" class="form-control" name="folio_garantia" value="{{ old('folio_garantia') }}">
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="tipo_atencion">Tipo de Atención *</label>
                                        <select class="form-control" name="tipo_atencion" required>
                                            <option value="">Seleccionar...</option>
                                            <option value="taller">Taller</option>
                                            <option value="terreno">Terreno</option>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="cliente_id">Cliente *</label>
                                        <select class="form-control" name="cliente_id" id="cliente_id" required>
                                            <option value="">Seleccionar cliente...</option>
                                            @foreach($clientes as $cliente)
                                                <option value="{{ $cliente->id }}">{{ $cliente->rut ? $cliente->rut . ' - ' : '' }}{{ $cliente->nombre }} {{ $cliente->apellido }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="artefacto_id">Artefacto</label>
                                        <select class="form-control" name="artefacto_id" id="artefacto_id">
                                            <option value="">Seleccionar artefacto...</option>
                                            @foreach($artefactos as $artefacto)
                                                <option value="{{ $artefacto->id }}">{{ $artefacto->nombre }} - {{ $artefacto->marca }} {{ $artefacto->modelo }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div id="fecha-visita-container" class="col-md-6" style="display:none">
                                    <div class="form-group">
                                        <label for="fecha_visita">Fecha de Visita</label>
                                        <input type="datetime-local" class="form-control" name="fecha_visita" value="{{ old('fecha_visita') }}">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="tecnico_id">Técnico</label>
                                        <select class="form-control" name="tecnico_id">
                                            <option value="">Seleccionar técnico...</option>
                                            @foreach($tecnicos as $tecnico)
                                                <option value="{{ $tecnico->id }}">{{ $tecnico->nombre }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <div class="form-group">
                                <label for="descripcion_falla">Descripción de la Falla *</label>
                                <textarea class="form-control" name="descripcion_falla" rows="3" required>{{ old('descripcion_falla') }}</textarea>
                            </div>

                            <div class="form-group">
                                <label for="observaciones">Observaciones</label>
                                <textarea class="form-control" name="observaciones" rows="2">{{ old('observaciones') }}</textarea>
                            </div>

                        </div>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Servicios y Productos</h3>
                        </div>
                        <div class="card-body">
                            <div id="valor-visita-container" style="display:none">
                                <div class="form-group">
                                    <label for="valor_visita">Valor de Visita</label>
                                    <input type="number" class="form-control" name="valor_visita" step="0.01" min="0" value="{{ old('valor_visita') }}" placeholder="0">
                                </div>
                            </div>

                            <div class="form-group">
                                <label>Agregar Item</label>
                                <div class="input-group">
                                    <select class="form-control" id="item-select">
                                        <option value="">Seleccionar...</option>
                                        <optgroup label="Servicios">
                                            @foreach($servicios as $servicio)
                                                <option value="servicio-{{ $servicio->id }}" data-precio="{{ $servicio->precio }}">
                                                    {{ $servicio->nombre_servicio }} - ${{ number_format($servicio->precio, 0, ',', '.') }}
                                                </option>
                                            @endforeach
                                        </optgroup>
                                        <optgroup label="Productos">
                                            @foreach($productos as $producto)
                                                <option value="producto-{{ $producto->id }}" data-precio="0">
                                                    {{ $producto->name }}
                                                </option>
                                            @endforeach
                                        </optgroup>
                                    </select>
                                    <div class="input-group-append">
                                        <button type="button" class="btn btn-primary" id="add-item">Agregar</button>
                                    </div>
                                </div>
                            </div>

                            <div id="items-list">
                                <!-- Items agregados aparecerán aquí -->
                            </div>

                            <div class="mt-3">
                                <strong>Total: $<span id="total-amount">0</span></strong>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-footer">
                            <button type="submit" class="btn btn-primary">Crear Orden</button>
                            <a href="{{ route('ordenes_servicio.index') }}" class="btn btn-secondary">Cancelar</a>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>

@push('page_scripts')
<script>
$(document).ready(function() {
    let itemCounter = 0;
    
    $('#cliente_id').select2();
    $('#artefacto_id').select2();
    $('#item-select').select2();



    $('select[name="tipo_atencion"]').on('change', function() {
        if ($(this).val() === 'terreno') {
            $('#fecha-visita-container').show();
            $('#valor-visita-container').show();
        } else {
            $('#fecha-visita-container').hide();
            $('input[name="fecha_visita"]').val('');
            $('#valor-visita-container').hide();
            $('input[name="valor_visita"]').val('0');
            updateTotal();
        }
    });

    $('#add-item').click(function() {
        const selected = $('#item-select');
        const value = selected.val();
        const text = selected.find('option:selected').text();
        const precio = selected.find('option:selected').data('precio') || 0;
        
        if (!value) return;

        const [tipo, id] = value.split('-');
        
        const itemHtml = `
            <div class="item-row border p-2 mb-2" data-counter="${itemCounter}">
                <input type="hidden" name="detalles[${itemCounter}][tipo]" value="${tipo}">
                <input type="hidden" name="detalles[${itemCounter}][id]" value="${id}">
                
                <div class="row">
                    <div class="col-12">
                        <strong>${text}</strong>
                        <button type="button" class="btn btn-sm btn-danger float-right remove-item">×</button>
                    </div>
                </div>
                
                <div class="row mt-2">
                    <div class="col-4">
                        <label>Cantidad</label>
                        <input type="number" class="form-control cantidad" name="detalles[${itemCounter}][cantidad]" value="1" min="1">
                    </div>
                    <div class="col-4">
                        <label>Precio</label>
                        <input type="number" class="form-control precio" name="detalles[${itemCounter}][precio]" value="${precio}" step="0.01" min="0">
                    </div>
                    <div class="col-4">
                        <label>Subtotal</label>
                        <input type="text" class="form-control subtotal" readonly value="${precio}">
                    </div>
                </div>
                
                <div class="row mt-2">
                    <div class="col-12">
                        <label>Nota</label>
                        <input type="text" class="form-control" name="detalles[${itemCounter}][nota]" placeholder="Nota opcional">
                    </div>
                </div>
            </div>
        `;
        
        $('#items-list').append(itemHtml);
        itemCounter++;
        selected.val('').trigger('change');
        updateTotal();
    });

    $(document).on('click', '.remove-item', function() {
        $(this).closest('.item-row').remove();
        updateTotal();
    });

    $(document).on('input', '.cantidad, .precio', function() {
        const row = $(this).closest('.item-row');
        const cantidad = parseFloat(row.find('.cantidad').val()) || 0;
        const precio = parseFloat(row.find('.precio').val()) || 0;
        const subtotal = cantidad * precio;
        
        row.find('.subtotal').val(subtotal.toFixed(2));
        updateTotal();
    });

    function updateTotal() {
        let total = 0;
        $('.subtotal').each(function() {
            total += parseFloat($(this).val()) || 0;
        });
        
        const valorVisita = parseFloat($('input[name="valor_visita"]').val()) || 0;
        total += valorVisita;
        
        $('#total-amount').text(total.toLocaleString('es-CL'));
    }

    $('input[name="valor_visita"]').on('input', updateTotal);
});
</script>
@endpush
@endsection