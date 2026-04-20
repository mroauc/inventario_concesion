@extends('layouts.app')

@section('content')
<section class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="text-dark font-weight-bold">Crear Orden de Servicio</h1>
            </div>
            <div class="col-sm-6 text-right">
                <span class="badge badge-primary px-3 py-2" style="font-size:1rem; letter-spacing:0.05em; background-color:#132a56;">
                    Folio: OT-{{ str_pad($proximoNumero, 6, '0', STR_PAD_LEFT) }}
                </span>
            </div>
        </div>
    </div>
</section>

<div class="content px-3">
    @include('flash-message')

    <form method="POST" action="{{ route('ordenes_servicio.store') }}" id="form-orden">
        @csrf

        {{-- FILA 1: Cliente | Orden --}}
        <div class="row">

            {{-- CARD: CLIENTE --}}
            <div class="col-md-6">
                <div class="card card-outline card-primary card-brand-top shadow-sm mb-4">
                    <div class="card-header">
                        <h3 class="card-title font-weight-semibold">
                            <i class="fas fa-user mr-2 text-brand"></i>Cliente
                        </h3>
                    </div>
                    <div class="card-body">
                        <div class="form-group">
                            <label class="font-weight-medium">Cliente <span class="text-danger">*</span></label>
                            <select class="form-control select2" name="cliente_id" id="cliente_id" required>
                                <option value="">Buscar cliente...</option>
                                @foreach($clientes as $cliente)
                                    <option value="{{ $cliente->id }}">
                                        {{ $cliente->rut ? $cliente->rut . ' – ' : '' }}{{ $cliente->nombre }} {{ $cliente->apellido }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        {{-- Panel datos cliente --}}
                        <div id="cliente-info" class="mt-3" style="display:none;">
                            <hr class="mt-2 mb-2">
                            <div class="row text-sm">
                                <div class="col-6">
                                    <small class="text-muted d-block">RUT</small>
                                    <span id="ci-rut" class="font-weight-medium">—</span>
                                </div>
                                <div class="col-6">
                                    <small class="text-muted d-block">Nombre</small>
                                    <span id="ci-nombre" class="font-weight-medium">—</span>
                                </div>
                            </div>
                            <div class="row text-sm mt-2">
                                <div class="col-6">
                                    <small class="text-muted d-block">Teléfono</small>
                                    <span id="ci-telefono" class="font-weight-medium">—</span>
                                </div>
                                <div class="col-6">
                                    <small class="text-muted d-block">Email</small>
                                    <span id="ci-email" class="font-weight-medium">—</span>
                                </div>
                            </div>
                            <div class="row text-sm mt-2">
                                <div class="col-12">
                                    <small class="text-muted d-block">Dirección</small>
                                    <span id="ci-direccion" class="font-weight-medium">—</span>
                                    <span id="ci-ciudad" class="text-muted ml-1"></span>
                                </div>
                            </div>
                        </div>
                        <hr class="mt-2 mb-2">
                        <div class="form-group mb-0">
                            <label class="font-weight-medium">Artefacto</label>
                            @php
                                $artefactosPorTipo = $artefactos->groupBy(fn($a) =>
                                    $a->tipoArtefacto ? $a->tipoArtefacto->nombre : 'Sin tipo'
                                );
                            @endphp
                            <select class="form-control select2" name="artefacto_id" id="artefacto_id">
                                <option value="">Seleccionar artefacto...</option>
                                @foreach($artefactosPorTipo as $tipoNombre => $grupo)
                                    <optgroup label="{{ $tipoNombre }}">
                                        @foreach($grupo as $artefacto)
                                            <option value="{{ $artefacto->id }}"
                                                {{ old('artefacto_id') == $artefacto->id ? 'selected' : '' }}>
                                                @if($artefacto->marca && $artefacto->modelo)
                                                    {{ $artefacto->marca }} {{ $artefacto->modelo }}
                                                @elseif($artefacto->modelo)
                                                    {{ $artefacto->modelo }}
                                                @elseif($artefacto->marca)
                                                    {{ $artefacto->marca }}{{ $artefacto->descripcion ? ' – '.$artefacto->descripcion : '' }}
                                                @else
                                                    {{ $artefacto->descripcion ?? 'Sin identificar' }}
                                                @endif
                                            </option>
                                        @endforeach
                                    </optgroup>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            {{-- CARD: DETALLE DE ORDEN --}}
            <div class="col-md-6">
                <div class="card card-outline card-info card-brand-top shadow-sm mb-4">
                    <div class="card-header">
                        <h3 class="card-title font-weight-semibold">
                            <i class="fas fa-clipboard-list mr-2 text-brand"></i>Detalle de la Orden
                        </h3>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-6">
                                <div class="form-group">
                                    <label class="font-weight-medium">N° de Orden</label>
                                    <input type="text" class="form-control bg-light" value="#{{ $proximoNumero }}" readonly disabled>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="form-group">
                                    <label class="font-weight-medium">Folio Garantía</label>
                                    <input type="text" class="form-control" name="folio_garantia" value="{{ old('folio_garantia') }}" placeholder="Opcional">
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-6">
                                <div class="form-group">
                                    <label class="font-weight-medium">Tipo de Atención <span class="text-danger">*</span></label>
                                    <select class="form-control" name="tipo_atencion" id="tipo_atencion" required>
                                        <option value="">Seleccionar...</option>
                                        <option value="taller"  {{ old('tipo_atencion') == 'taller'  ? 'selected' : '' }}>Taller</option>
                                        <option value="terreno" {{ old('tipo_atencion') == 'terreno' ? 'selected' : '' }}>Terreno</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="form-group">
                                    <label class="font-weight-medium">Técnico</label>
                                    <select class="form-control select2" name="tecnico_id">
                                        <option value="">Sin asignar</option>
                                        @foreach($tecnicos as $tecnico)
                                            <option value="{{ $tecnico->id }}">{{ $tecnico->nombre }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>

                        {{-- Campos terreno --}}
                        <div class="row" id="terreno-fields" style="display:none;">
                            <div class="col-6">
                                <div class="form-group">
                                    <label class="font-weight-medium">Fecha de Visita</label>
                                    <input type="datetime-local" class="form-control" name="fecha_visita" value="{{ old('fecha_visita') }}">
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="form-group">
                                    <label class="font-weight-medium">Valor Visita</label>
                                    <div class="input-group">
                                        <div class="input-group-prepend"><span class="input-group-text">$</span></div>
                                        <input type="number" class="form-control" name="valor_visita" id="valor_visita"
                                               step="0.01" min="0" value="{{ old('valor_visita', 0) }}" placeholder="0">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="font-weight-medium">Descripción de la Falla <span class="text-danger">*</span></label>
                            <textarea class="form-control" name="descripcion_falla" rows="2" required
                                      placeholder="Describa el problema reportado...">{{ old('descripcion_falla') }}</textarea>
                        </div>
                        <div class="form-group mb-0">
                            <label class="font-weight-medium">Observaciones</label>
                            <textarea class="form-control" name="observaciones" rows="2"
                                      placeholder="Notas internas opcionales...">{{ old('observaciones') }}</textarea>
                        </div>
                    </div>
                </div>
            </div>

        </div>{{-- /fila 1 --}}

        {{-- FILA 2: Productos a ancho completo --}}
        <div class="row">
            <div class="col-12">
                <div class="card card-outline card-success card-brand-top shadow-sm mb-4">
                    <div class="card-header d-flex align-items-center justify-content-between">
                        <h3 class="card-title font-weight-semibold mb-0">
                            <i class="fas fa-box mr-2 text-brand"></i>Productos / Servicios
                        </h3>
                        <div class="input-group" style="max-width:420px;">
                            <select class="form-control select2" id="item-select">
                                <option value="">Seleccionar item para agregar...</option>
                                <optgroup label="Servicios">
                                    @foreach($servicios as $servicio)
                                        <option value="servicio-{{ $servicio->id }}" data-precio="{{ $servicio->precio }}">
                                            {{ $servicio->nombre_servicio }} – ${{ number_format($servicio->precio, 0, ',', '.') }}
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
                                <button type="button" class="btn btn-brand" id="add-item">
                                    <i class="fas fa-plus mr-1"></i>Agregar
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0" id="items-table">
                                <thead class="thead-light">
                                    <tr>
                                        <th class="pl-3">Item</th>
                                        <th>Nota</th>
                                        <th class="text-center" style="width:90px;">Cant.</th>
                                        <th class="text-right" style="width:130px;">Precio Unit.</th>
                                        <th class="text-right" style="width:120px;">Total</th>
                                        <th style="width:44px;"></th>
                                    </tr>
                                </thead>
                                <tbody id="items-tbody">
                                    <tr id="items-empty-row">
                                        <td colspan="6" class="text-center text-muted py-4">
                                            <i class="fas fa-inbox fa-2x mb-2 d-block"></i>
                                            Ningún item agregado. Selecciona un servicio o producto arriba.
                                        </td>
                                    </tr>
                                </tbody>
                                <tfoot id="items-tfoot" class="thead-light" style="display:none;">
                                    <tr>
                                        <td colspan="3" class="text-right pr-3">
                                            <span id="fila-visita-label" style="display:none;" class="text-muted mr-4">
                                                Valor visita: $<span id="resumen-visita">0</span>
                                            </span>
                                        </td>
                                        <td class="text-right font-weight-bold">Total:</td>
                                        <td class="text-right font-weight-bold text-success" style="font-size:1.05rem;">
                                            $<span id="total-amount">0</span>
                                        </td>
                                        <td></td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>

                    <div class="card-footer d-flex justify-content-end gap-2">
                        <a href="{{ route('ordenes_servicio.index') }}" class="btn btn-secondary mr-2">
                            <i class="fas fa-times mr-1"></i>Cancelar
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save mr-1"></i>Guardar Orden
                        </button>
                    </div>
                </div>
            </div>
        </div>{{-- /fila 2 --}}

    </form>
</div>

@push('page_scripts')
<script>
$(document).ready(function () {

    // ── Select2 ──────────────────────────────────────────────────────────────
    $('#cliente_id, #artefacto_id, #item-select, select[name="tecnico_id"]').select2({ width: '100%' });

    // ── Datos cliente AJAX ────────────────────────────────────────────────────
    function cargarDatosCliente(clienteId) {
        if (!clienteId) {
            $('#cliente-info').hide();
            return;
        }
        $.getJSON("{{url('/clientes')}}/" + clienteId + '/datos', function (data) {
            $('#ci-rut').text(data.rut || '—');
            $('#ci-nombre').text(data.nombre || '—');
            $('#ci-telefono').text(data.numero_contacto || '—');
            $('#ci-email').text(data.email || '—');
            $('#ci-direccion').text(data.direccion || '—');
            $('#ci-ciudad').text(data.ciudad ? '(' + data.ciudad + ')' : '');
            $('#cliente-info').show();
        });
    }
    $('#cliente_id').on('change', function () { cargarDatosCliente($(this).val()); });

    // ── Tipo atención ─────────────────────────────────────────────────────────
    function toggleTerrenoFields() {
        const isTerreno = $('#tipo_atencion').val() === 'terreno';
        $('#terreno-fields').toggle(isTerreno);
        $('#fila-visita-label').toggle(isTerreno);
        if (!isTerreno) {
            $('input[name="fecha_visita"]').val('');
            $('#valor_visita').val('0');
            updateTotal();
        }
    }
    $('#tipo_atencion').on('change', toggleTerrenoFields);
    toggleTerrenoFields();

    // ── Agregar item ──────────────────────────────────────────────────────────
    let itemCounter = 0;

    $('#add-item').on('click', function () {
        const $sel  = $('#item-select');
        const value = $sel.val();
        if (!value) return;

        const text   = $sel.find('option:selected').text().split(' – ')[0].trim();
        const precio = parseFloat($sel.find('option:selected').data('precio')) || 0;
        const [tipo, id] = value.split('-');
        const idx = itemCounter++;

        const $row = $(`
            <tr class="item-row">
                <td class="pl-3 align-middle font-weight-medium">
                    <input type="hidden" name="detalles[${idx}][tipo]" value="${tipo}">
                    <input type="hidden" name="detalles[${idx}][id]"   value="${id}">
                    ${text}
                </td>
                <td class="align-middle">
                    <input type="text" class="form-control form-control-sm"
                           name="detalles[${idx}][nota]" placeholder="Nota opcional">
                </td>
                <td class="text-center align-middle">
                    <input type="number" class="form-control form-control-sm text-center item-cant"
                           name="detalles[${idx}][cantidad]" value="1" min="1">
                </td>
                <td class="text-right align-middle">
                    <input type="number" class="form-control form-control-sm text-right item-precio"
                           name="detalles[${idx}][precio]" value="${precio}" step="0.01" min="0">
                </td>
                <td class="text-right align-middle font-weight-medium item-subtotal">
                    $${formatCLP(precio)}
                </td>
                <td class="text-center align-middle">
                    <button type="button" class="btn btn-sm btn-outline-danger remove-item" title="Eliminar">
                        <i class="fas fa-trash-alt"></i>
                    </button>
                </td>
            </tr>
        `);

        $('#items-empty-row').hide();
        $('#items-tbody').append($row);
        $('#items-tfoot').show();
        updateTotal();
        $sel.val('').trigger('change');
    });

    // ── Eliminar item ─────────────────────────────────────────────────────────
    $(document).on('click', '.remove-item', function () {
        $(this).closest('.item-row').remove();
        if ($('.item-row').length === 0) {
            $('#items-empty-row').show();
            $('#items-tfoot').hide();
        }
        updateTotal();
    });

    // ── Recalcular subtotal por fila ──────────────────────────────────────────
    $(document).on('input', '.item-cant, .item-precio', function () {
        const $row   = $(this).closest('.item-row');
        const cant   = parseFloat($row.find('.item-cant').val())   || 0;
        const precio = parseFloat($row.find('.item-precio').val()) || 0;
        $row.find('.item-subtotal').text('$' + formatCLP(cant * precio));
        updateTotal();
    });

    // ── Valor visita ──────────────────────────────────────────────────────────
    $('#valor_visita').on('input', function () {
        $('#resumen-visita').text(formatCLP(parseFloat($(this).val()) || 0));
        updateTotal();
    });

    // ── Total general ─────────────────────────────────────────────────────────
    function updateTotal() {
        let subtotal = 0;
        $('.item-row').each(function () {
            const cant   = parseFloat($(this).find('.item-cant').val())   || 0;
            const precio = parseFloat($(this).find('.item-precio').val()) || 0;
            subtotal += cant * precio;
        });
        const visita = parseFloat($('#valor_visita').val()) || 0;
        $('#total-amount').text(formatCLP(subtotal + visita));
        $('#resumen-visita').text(formatCLP(visita));
    }

    // ── Formato CLP ───────────────────────────────────────────────────────────
    function formatCLP(n) {
        return Math.round(n).toLocaleString('es-CL');
    }
});
</script>
@endpush
@endsection
