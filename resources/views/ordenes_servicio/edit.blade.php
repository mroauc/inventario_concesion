@extends('layouts.app')

@section('content')
<section class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="text-dark font-weight-bold">Editar Orden de Servicio</h1>
            </div>
            <div class="col-sm-6 text-right">
                <span class="badge badge-primary px-3 py-2" style="font-size:1rem; letter-spacing:0.05em; background-color:#132a56;">
                    Folio: OT-{{ str_pad($orden->numero, 6, '0', STR_PAD_LEFT) }}
                </span>
            </div>
        </div>
    </div>
</section>

<div class="content px-3">
    @include('flash-message')

    <form method="POST" action="{{ route('ordenes_servicio.update', $orden->id) }}" id="form-orden">
        @csrf
        @method('PUT')

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
                                @foreach($clientes as $cliente)
                                    <option value="{{ $cliente->id }}" {{ $orden->cliente_id == $cliente->id ? 'selected' : '' }}>
                                        {{ $cliente->rut ? $cliente->rut . ' – ' : '' }}{{ $cliente->nombre }} {{ $cliente->apellido }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        {{-- Panel datos cliente --}}
                        <div id="cliente-info" class="mt-3">
                            <hr class="mt-2 mb-2">
                            <div class="row text-sm">
                                <div class="col-6">
                                    <small class="text-muted d-block">RUT</small>
                                    <span id="ci-rut" class="font-weight-medium">{{ $orden->cliente->rut ?? '—' }}</span>
                                </div>
                                <div class="col-6">
                                    <small class="text-muted d-block">Nombre</small>
                                    <span id="ci-nombre" class="font-weight-medium">{{ $orden->cliente ? $orden->cliente->nombre . ' ' . $orden->cliente->apellido : '—' }}</span>
                                </div>
                            </div>
                            <div class="row text-sm mt-2">
                                <div class="col-6">
                                    <small class="text-muted d-block">Teléfono</small>
                                    <span id="ci-telefono" class="font-weight-medium">{{ $orden->cliente->numero_contacto ?? '—' }}</span>
                                </div>
                                <div class="col-6">
                                    <small class="text-muted d-block">Email</small>
                                    <span id="ci-email" class="font-weight-medium">{{ $orden->cliente->email ?? '—' }}</span>
                                </div>
                            </div>
                            <div class="row text-sm mt-2">
                                <div class="col-12">
                                    <small class="text-muted d-block">Dirección</small>
                                    <span id="ci-direccion" class="font-weight-medium">{{ $orden->cliente->direccion ?? '—' }}</span>
                                    <span id="ci-ciudad" class="text-muted ml-1">{{ $orden->cliente->ciudad ? '(' . $orden->cliente->ciudad . ')' : '' }}</span>
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
                            <select class="form-control select2" name="artefacto_id">
                                <option value="">Seleccionar artefacto...</option>
                                @foreach($artefactosPorTipo as $tipoNombre => $grupo)
                                    <optgroup label="{{ $tipoNombre }}">
                                        @foreach($grupo as $artefacto)
                                            @php
                                                if ($artefacto->marca && $artefacto->modelo)
                                                    $label = $artefacto->marca . ' ' . $artefacto->modelo;
                                                elseif ($artefacto->modelo)
                                                    $label = $artefacto->modelo;
                                                elseif ($artefacto->marca)
                                                    $label = $artefacto->marca . ($artefacto->descripcion ? ' – ' . $artefacto->descripcion : '');
                                                else
                                                    $label = $artefacto->descripcion ?? 'Sin identificar';
                                            @endphp
                                            <option value="{{ $artefacto->id }}"
                                                data-codigo="{{ $artefacto->codigo ?? '' }}"
                                                data-marca="{{ $artefacto->marca ?? '' }}"
                                                data-modelo="{{ $artefacto->modelo ?? '' }}"
                                                data-descripcion="{{ $artefacto->descripcion ?? '' }}"
                                                data-tipo="{{ $artefacto->tipoArtefacto ? $artefacto->tipoArtefacto->nombre : '' }}"
                                                {{ (old('artefacto_id', $orden->artefacto_id) == $artefacto->id) ? 'selected' : '' }}>
                                                {{ $label }}{{ $artefacto->codigo ? ' ('.$artefacto->codigo.')' : '' }}
                                            </option>
                                        @endforeach
                                    </optgroup>
                                @endforeach
                            </select>
                        </div>

                        {{-- Panel detalle artefacto --}}
                        @php
                            $a = $orden->artefacto;
                        @endphp
                        <div id="artefacto-info" class="mt-3" style="{{ $a ? '' : 'display:none;' }}">
                            <hr class="mt-2 mb-2">
                            <div class="row text-sm">
                                <div class="col-6">
                                    <small class="text-muted d-block">Código</small>
                                    <span id="ai-codigo" class="font-weight-medium">{{ $a->codigo ?? '—' }}</span>
                                </div>
                                <div class="col-6">
                                    <small class="text-muted d-block">Tipo</small>
                                    <span id="ai-tipo" class="font-weight-medium">{{ $a && $a->tipoArtefacto ? $a->tipoArtefacto->nombre : '—' }}</span>
                                </div>
                            </div>
                            <div class="row text-sm mt-2">
                                <div class="col-6">
                                    <small class="text-muted d-block">Marca</small>
                                    <span id="ai-marca" class="font-weight-medium">{{ $a->marca ?? '—' }}</span>
                                </div>
                                <div class="col-6">
                                    <small class="text-muted d-block">Modelo</small>
                                    <span id="ai-modelo" class="font-weight-medium">{{ $a->modelo ?? '—' }}</span>
                                </div>
                            </div>
                            <div class="row text-sm mt-2">
                                <div class="col-12">
                                    <small class="text-muted d-block">Descripción</small>
                                    <span id="ai-descripcion" class="font-weight-medium">{{ $a->descripcion ?? '—' }}</span>
                                </div>
                            </div>
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
                                    <input type="text" class="form-control bg-light" value="#{{ $orden->numero }}" readonly disabled>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="form-group">
                                    <label class="font-weight-medium">Tipo de Asistencia</label>
                                    <select class="form-control" name="tipo_asistencia" id="tipo_asistencia">
                                        <option value="">Seleccionar...</option>
                                        <option value="garantia_extendida" {{ old('tipo_asistencia', $orden->tipo_asistencia) == 'garantia_extendida' ? 'selected' : '' }}>Asistencia garantía extendida</option>
                                        <option value="garantia_fabrica"   {{ old('tipo_asistencia', $orden->tipo_asistencia) == 'garantia_fabrica'   ? 'selected' : '' }}>Asistencia garantía fábrica</option>
                                        <option value="garantia_trabajo"   {{ old('tipo_asistencia', $orden->tipo_asistencia) == 'garantia_trabajo'   ? 'selected' : '' }}>Asistencia garantía de trabajo</option>
                                        <option value="fuera_garantia"     {{ old('tipo_asistencia', $orden->tipo_asistencia) == 'fuera_garantia'     ? 'selected' : '' }}>Asistencia fuera garantía</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        @php
                            $mostrarFolio = in_array(old('tipo_asistencia', $orden->tipo_asistencia), ['garantia_extendida', 'garantia_fabrica']);
                        @endphp
                        <div class="row" id="folio-garantia-row" style="{{ $mostrarFolio ? '' : 'display:none;' }}">
                            <div class="col-12">
                                <div class="form-group">
                                    <label class="font-weight-medium">Folio Garantía</label>
                                    <input type="text" class="form-control" name="folio_garantia"
                                           value="{{ old('folio_garantia', $orden->folio_garantia) }}" placeholder="Opcional">
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-4">
                                <div class="form-group">
                                    <label class="font-weight-medium">Tipo Atención <span class="text-danger">*</span></label>
                                    <select class="form-control" name="tipo_atencion" id="tipo_atencion" required>
                                        <option value="taller"  {{ $orden->tipo_atencion == 'taller'  ? 'selected' : '' }}>Taller</option>
                                        <option value="terreno" {{ $orden->tipo_atencion == 'terreno' ? 'selected' : '' }}>Terreno</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="form-group">
                                    <label class="font-weight-medium">Estado <span class="text-danger">*</span></label>
                                    <select class="form-control" name="estado" required>
                                        <option value="pendiente"   {{ $orden->estado == 'pendiente'   ? 'selected' : '' }}>Pendiente</option>
                                        <option value="en_progreso" {{ $orden->estado == 'en_progreso' ? 'selected' : '' }}>En Progreso</option>
                                        <option value="finalizada"  {{ $orden->estado == 'finalizada'  ? 'selected' : '' }}>Finalizada</option>
                                        <option value="cancelada"   {{ $orden->estado == 'cancelada'   ? 'selected' : '' }}>Cancelada</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="form-group">
                                    <label class="font-weight-medium">Técnico</label>
                                    <select class="form-control select2" name="tecnico_id">
                                        <option value="">Sin asignar</option>
                                        @foreach($tecnicos as $tecnico)
                                            <option value="{{ $tecnico->id }}" {{ $orden->tecnico_id == $tecnico->id ? 'selected' : '' }}>
                                                {{ $tecnico->nombre }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>

                        {{-- Campos terreno --}}
                        <div class="row" id="terreno-fields"
                             style="{{ $orden->tipo_atencion == 'terreno' ? '' : 'display:none;' }}">
                            <div class="col-6">
                                <div class="form-group">
                                    <label class="font-weight-medium">Fecha de Visita</label>
                                    <input type="datetime-local" class="form-control" name="fecha_visita"
                                           value="{{ old('fecha_visita', $orden->fecha_visita ? $orden->fecha_visita->format('Y-m-d\TH:i') : '') }}">
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="form-group">
                                    <label class="font-weight-medium">Valor Visita</label>
                                    <div class="input-group">
                                        <div class="input-group-prepend"><span class="input-group-text">$</span></div>
                                        <input type="number" class="form-control" name="valor_visita"
                                               step="0.01" min="0"
                                               value="{{ old('valor_visita', $orden->valor_visita) }}" placeholder="0">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="font-weight-medium">Descripción de la Falla <span class="text-danger">*</span></label>
                            <textarea class="form-control" name="descripcion_falla" rows="2" required
                                      placeholder="Describa el problema reportado...">{{ old('descripcion_falla', $orden->descripcion_falla) }}</textarea>
                        </div>
                        <div class="form-group mb-0">
                            <label class="font-weight-medium">Observaciones</label>
                            <textarea class="form-control" name="observaciones" rows="2"
                                      placeholder="Notas internas opcionales...">{{ old('observaciones', $orden->observaciones) }}</textarea>
                        </div>
                    </div>
                </div>
            </div>

        </div>{{-- /fila 1 --}}

        {{-- FILA 2: Detalles del servicio (editable) --}}
        <div class="row">
            <div class="col-12">
                <div class="card card-outline card-warning card-brand-top shadow-sm mb-4">
                    <div class="card-header">
                        <h3 class="card-title font-weight-semibold mb-0">
                            <i class="fas fa-list-ul mr-2 text-brand"></i>Detalles del Servicio
                        </h3>
                    </div>
                    <div class="card-body border-bottom pb-3">
                        <div class="d-flex align-items-end gap-2">
                            <div>
                                <label class="font-weight-medium mb-1 d-block" style="font-size:.85rem;">Producto / Servicio</label>
                                <select id="item-select" class="form-control select2-item" style="width:600px;">
                                    <option value="">Seleccionar...</option>
                                    <optgroup label="Servicios">
                                        @foreach($servicios as $s)
                                            <option value="{{ $s->id }}" data-tipo="servicio" data-nombre="{{ $s->nombre_servicio }}" data-precio="{{ $s->precio ?? 0 }}">
                                                {{ $s->nombre_servicio }}
                                            </option>
                                        @endforeach
                                    </optgroup>
                                    <optgroup label="Productos">
                                        @foreach($productos as $p)
                                            <option value="{{ $p->id }}" data-tipo="producto" data-nombre="{{ $p->name }}" data-precio="{{ $p->price ?? 0 }}">
                                                {{ $p->code ? '[' . $p->code . '] ' : '' }}{{ $p->name }}
                                            </option>
                                        @endforeach
                                    </optgroup>
                                </select>
                            </div>
                            <div>
                                <label class="d-block mb-1" style="font-size:.85rem;">&nbsp;</label>
                                <button type="button" id="btn-agregar-item" class="btn btn-success btn-sm">
                                    <i class="fas fa-plus mr-1"></i>Agregar
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0" id="tabla-detalles">
                                <thead class="thead-light">
                                    <tr>
                                        <th class="pl-3">Item</th>
                                        <th style="width:100px;">Tipo</th>
                                        <th class="text-center" style="width:90px;">Cant.</th>
                                        <th class="text-right" style="width:150px;">Precio Unit.</th>
                                        <th class="text-right" style="width:130px;">Subtotal</th>
                                        <th>Nota</th>
                                        <th style="width:50px;"></th>
                                    </tr>
                                </thead>
                                <tbody id="detalles-tbody">
                                    @forelse($orden->detalles as $i => $detalle)
                                    @php
                                        $tipo = $detalle->producto ? 'producto' : 'servicio';
                                        $itemId = $detalle->producto_id ?? $detalle->servicio_id;
                                        $nombre = $detalle->producto ? $detalle->producto->name : ($detalle->servicio ? $detalle->servicio->nombre_servicio : '—');
                                    @endphp
                                    <tr data-idx="{{ $i }}">
                                        <td class="pl-3 font-weight-medium">
                                            {{ $nombre }}
                                            <input type="hidden" name="detalles[{{ $i }}][tipo]" value="{{ $tipo }}">
                                            <input type="hidden" name="detalles[{{ $i }}][id]" value="{{ $itemId }}">
                                        </td>
                                        <td>
                                            <span class="badge {{ $tipo === 'producto' ? 'badge-info' : 'badge-success' }}">
                                                {{ $tipo === 'producto' ? 'Producto' : 'Servicio' }}
                                            </span>
                                        </td>
                                        <td class="text-center">
                                            <input type="number" name="detalles[{{ $i }}][cantidad]"
                                                   class="form-control form-control-sm text-center input-cantidad"
                                                   value="{{ $detalle->cantidad }}" min="1" style="width:70px;">
                                        </td>
                                        <td class="text-right">
                                            <div class="input-group input-group-sm justify-content-end">
                                                <div class="input-group-prepend"><span class="input-group-text">$</span></div>
                                                <input type="number" name="detalles[{{ $i }}][precio]"
                                                       class="form-control form-control-sm text-right input-precio"
                                                       value="{{ $detalle->precio_unitario }}" min="0" style="width:100px;">
                                            </div>
                                        </td>
                                        <td class="text-right font-weight-medium subtotal-cell">
                                            ${{ number_format($detalle->subtotal, 0, ',', '.') }}
                                        </td>
                                        <td>
                                            <input type="text" name="detalles[{{ $i }}][nota]"
                                                   class="form-control form-control-sm"
                                                   value="{{ $detalle->nota }}" placeholder="Nota opcional">
                                        </td>
                                        <td class="text-center">
                                            <button type="button" class="btn btn-danger btn-sm btn-eliminar-detalle">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </td>
                                    </tr>
                                    @empty
                                    <tr id="fila-vacia">
                                        <td colspan="7" class="text-center text-muted py-4">
                                            <i class="fas fa-inbox fa-2x mb-2 d-block"></i>
                                            Sin detalles. Agregue productos o servicios arriba.
                                        </td>
                                    </tr>
                                    @endforelse
                                </tbody>
                                <tfoot class="thead-light">
                                    <tr>
                                        <td colspan="4" class="text-right font-weight-bold">Total:</td>
                                        <td class="text-right font-weight-bold text-success" style="font-size:1.05rem;" id="total-general">
                                            ${{ number_format($orden->costo_total, 0, ',', '.') }}
                                        </td>
                                        <td colspan="2"></td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                    <div class="card-footer d-flex justify-content-end">
                        <a href="{{ route('ordenes_servicio.show', $orden->id) }}" class="btn btn-secondary mr-2">
                            <i class="fas fa-times mr-1"></i>Cancelar
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save mr-1"></i>Actualizar Orden
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
    $('select[name="cliente_id"], select[name="tecnico_id"]').select2({ width: '100%' });

    $('select[name="artefacto_id"]').select2({
        width: '100%',
        templateResult: function (option) {
            if (!option.id) return option.text;
            var codigo = $(option.element).data('codigo');
            if (codigo) {
                return $('<span>' + $('<span>').text(option.text.replace(' (' + codigo + ')', '')).text() + ' <small class="text-muted">(' + $('<span>').text(codigo).text() + ')</small></span>');
            }
            return option.text;
        },
        templateSelection: function (option) {
            if (!option.id) return option.text;
            var codigo = $(option.element).data('codigo');
            if (codigo) {
                return $('<span>' + $('<span>').text(option.text.replace(' (' + codigo + ')', '')).text() + ' <small class="text-muted">(' + $('<span>').text(codigo).text() + ')</small></span>');
            }
            return option.text;
        }
    });

    // ── Panel detalle artefacto ───────────────────────────────────────────────
    function actualizarPanelArtefacto() {
        const opt = $('select[name="artefacto_id"] option:selected');
        const id  = opt.val();
        if (!id) { $('#artefacto-info').hide(); return; }
        $('#ai-codigo').text(opt.data('codigo') || '—');
        $('#ai-tipo').text(opt.data('tipo') || '—');
        $('#ai-marca').text(opt.data('marca') || '—');
        $('#ai-modelo').text(opt.data('modelo') || '—');
        $('#ai-descripcion').text(opt.data('descripcion') || '—');
        $('#artefacto-info').show();
    }
    $('select[name="artefacto_id"]').on('change', actualizarPanelArtefacto);

    // ── Tipo asistencia → folio garantía ─────────────────────────────────────
    function toggleFolioGarantia() {
        const val = $('#tipo_asistencia').val();
        const mostrar = val === 'garantia_extendida' || val === 'garantia_fabrica';
        $('#folio-garantia-row').toggle(mostrar);
        if (!mostrar) $('input[name="folio_garantia"]').val('');
    }
    $('#tipo_asistencia').on('change', toggleFolioGarantia);

    // ── Datos cliente AJAX ────────────────────────────────────────────────────
    function cargarDatosCliente(clienteId) {
        if (!clienteId) return;
        $.getJSON('/clientes/' + clienteId + '/datos', function (data) {
            $('#ci-rut').text(data.rut || '—');
            $('#ci-nombre').text(data.nombre || '—');
            $('#ci-telefono').text(data.numero_contacto || '—');
            $('#ci-email').text(data.email || '—');
            $('#ci-direccion').text(data.direccion || '—');
            $('#ci-ciudad').text(data.ciudad ? '(' + data.ciudad + ')' : '');
        });
    }
    $('#cliente_id').on('change', function () { cargarDatosCliente($(this).val()); });

    // ── Tipo atención ─────────────────────────────────────────────────────────
    $('#tipo_atencion').on('change', function () {
        const isTerreno = $(this).val() === 'terreno';
        $('#terreno-fields').toggle(isTerreno);
        if (!isTerreno) {
            $('input[name="fecha_visita"]').val('');
            $('input[name="valor_visita"]').val('0');
        }
    });

    $('#item-select').select2({ width: '600px' });

    // ── Detalles: índice para nombres de campo ────────────────────────────────
    function nextIndex() {
        return $('#detalles-tbody tr[data-idx]').length > 0
            ? Math.max(...$('#detalles-tbody tr[data-idx]').map(function () { return parseInt($(this).data('idx')); }).get()) + 1
            : $('#detalles-tbody tr').length;
    }

    // ── Detalles: agregar fila ────────────────────────────────────────────────
    $('#btn-agregar-item').on('click', function () {
        const opt    = $('#item-select option:selected');
        const tipo   = opt.data('tipo') || 'producto';
        const itemId = opt.val();
        if (!itemId) { alert('Seleccione un item antes de agregar.'); return; }

        const nombre  = opt.data('nombre') || opt.text();
        const precio  = parseFloat(opt.data('precio')) || 0;
        const idx     = nextIndex();
        const badge   = tipo === 'producto'
            ? '<span class="badge badge-info">Producto</span>'
            : '<span class="badge badge-success">Servicio</span>';

        $('#fila-vacia').remove();

        const fila = `
        <tr data-idx="${idx}">
            <td class="pl-3 font-weight-medium">
                ${$('<span>').text(nombre).html()}
                <input type="hidden" name="detalles[${idx}][tipo]" value="${tipo}">
                <input type="hidden" name="detalles[${idx}][id]"   value="${itemId}">
            </td>
            <td>${badge}</td>
            <td class="text-center">
                <input type="number" name="detalles[${idx}][cantidad]"
                       class="form-control form-control-sm text-center input-cantidad"
                       value="1" min="1" style="width:70px;">
            </td>
            <td class="text-right">
                <div class="input-group input-group-sm justify-content-end">
                    <div class="input-group-prepend"><span class="input-group-text">$</span></div>
                    <input type="number" name="detalles[${idx}][precio]"
                           class="form-control form-control-sm text-right input-precio"
                           value="${precio}" min="0" style="width:100px;">
                </div>
            </td>
            <td class="text-right font-weight-medium subtotal-cell">$${formatNum(precio)}</td>
            <td>
                <input type="text" name="detalles[${idx}][nota]"
                       class="form-control form-control-sm" placeholder="Nota opcional">
            </td>
            <td class="text-center">
                <button type="button" class="btn btn-danger btn-sm btn-eliminar-detalle">
                    <i class="fas fa-trash"></i>
                </button>
            </td>
        </tr>`;

        $('#detalles-tbody').append(fila);
        recalcularTotal();
        $('#item-select').val('').trigger('change.select2');
    });

    // ── Detalles: eliminar fila ───────────────────────────────────────────────
    $(document).on('click', '.btn-eliminar-detalle', function () {
        $(this).closest('tr').remove();
        if ($('#detalles-tbody tr').length === 0) {
            $('#detalles-tbody').append(`
                <tr id="fila-vacia">
                    <td colspan="7" class="text-center text-muted py-4">
                        <i class="fas fa-inbox fa-2x mb-2 d-block"></i>
                        Sin detalles. Agregue productos o servicios arriba.
                    </td>
                </tr>`);
        }
        recalcularTotal();
    });

    // ── Detalles: recalcular subtotales y total ───────────────────────────────
    function formatNum(n) {
        return Math.round(n).toLocaleString('es-CL');
    }

    function recalcularTotal() {
        let total = 0;
        $('#detalles-tbody tr[data-idx]').each(function () {
            const qty   = parseFloat($(this).find('.input-cantidad').val()) || 0;
            const price = parseFloat($(this).find('.input-precio').val())   || 0;
            const sub   = qty * price;
            $(this).find('.subtotal-cell').text('$' + formatNum(sub));
            total += sub;
        });
        const visita = parseFloat($('input[name="valor_visita"]').val()) || 0;
        $('#total-general').text('$' + formatNum(total + visita));
    }

    $(document).on('input', '.input-cantidad, .input-precio', recalcularTotal);
    $('input[name="valor_visita"]').on('input', recalcularTotal);

    recalcularTotal();
});
</script>
@endpush
@endsection
