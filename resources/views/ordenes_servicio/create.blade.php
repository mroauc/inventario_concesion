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
                                                {{ old('artefacto_id') == $artefacto->id ? 'selected' : '' }}>
                                                {{ $label }}{{ $artefacto->codigo ? ' ('.$artefacto->codigo.')' : '' }}
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
                                    <label class="font-weight-medium">Tipo de Asistencia</label>
                                    <select class="form-control" name="tipo_asistencia" id="tipo_asistencia">
                                        <option value="">Seleccionar...</option>
                                        <option value="garantia_extendida" {{ old('tipo_asistencia') == 'garantia_extendida' ? 'selected' : '' }}>Asistencia garantía extendida</option>
                                        <option value="garantia_fabrica"   {{ old('tipo_asistencia') == 'garantia_fabrica'   ? 'selected' : '' }}>Asistencia garantía fábrica</option>
                                        <option value="garantia_trabajo"   {{ old('tipo_asistencia') == 'garantia_trabajo'   ? 'selected' : '' }}>Asistencia garantía de trabajo</option>
                                        <option value="fuera_garantia"     {{ old('tipo_asistencia') == 'fuera_garantia'     ? 'selected' : '' }}>Asistencia fuera garantía</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="row" id="folio-garantia-row" style="display:none;">
                            <div class="col-12">
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

        <div class="row">
            <div class="col-12 d-flex justify-content-end mb-4">
                <a href="{{ route('ordenes_servicio.index') }}" class="btn btn-secondary mr-2">
                    <i class="fas fa-times mr-1"></i>Cancelar
                </a>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save mr-1"></i>Guardar Orden
                </button>
            </div>
        </div>

    </form>
</div>

@push('page_scripts')
<script>
$(document).ready(function () {

    // ── Select2 ──────────────────────────────────────────────────────────────
    $('#cliente_id, select[name="tecnico_id"]').select2({ width: '100%' });

    $('#artefacto_id').select2({
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

    // ── Datos cliente AJAX ────────────────────────────────────────────────────
    function cargarDatosCliente(clienteId) {
        if (!clienteId) { $('#cliente-info').hide(); return; }
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

    // ── Tipo asistencia → folio garantía ─────────────────────────────────────
    function toggleFolioGarantia() {
        const val = $('#tipo_asistencia').val();
        const mostrar = val === 'garantia_extendida' || val === 'garantia_fabrica';
        $('#folio-garantia-row').toggle(mostrar);
        if (!mostrar) $('input[name="folio_garantia"]').val('');
    }
    $('#tipo_asistencia').on('change', toggleFolioGarantia);
    toggleFolioGarantia();

    // ── Tipo atención ─────────────────────────────────────────────────────────
    function toggleTerrenoFields() {
        const isTerreno = $('#tipo_atencion').val() === 'terreno';
        $('#terreno-fields').toggle(isTerreno);
        if (!isTerreno) $('input[name="fecha_visita"]').val('');
    }
    $('#tipo_atencion').on('change', toggleTerrenoFields);
    toggleTerrenoFields();
});
</script>
@endpush
@endsection
