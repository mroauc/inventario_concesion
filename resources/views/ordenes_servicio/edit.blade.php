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
                        <div class="form-group mb-0">
                            <label class="font-weight-medium">Artefacto</label>
                            <select class="form-control select2" name="artefacto_id">
                                <option value="">Seleccionar artefacto...</option>
                                @foreach($artefactos as $artefacto)
                                    <option value="{{ $artefacto->id }}" {{ $orden->artefacto_id == $artefacto->id ? 'selected' : '' }}>
                                        {{ $artefacto->nombre }} – {{ $artefacto->marca }} {{ $artefacto->modelo }}
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

        {{-- FILA 2: Productos a ancho completo --}}
        <div class="row">
            <div class="col-12">
                <div class="card card-outline card-warning card-brand-top shadow-sm mb-4">
                    <div class="card-header">
                        <h3 class="card-title font-weight-semibold">
                            <i class="fas fa-list-ul mr-2 text-brand"></i>Detalles del Servicio
                        </h3>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="thead-light">
                                    <tr>
                                        <th class="pl-3">Item</th>
                                        <th>Tipo</th>
                                        <th class="text-center" style="width:80px;">Cant.</th>
                                        <th class="text-right" style="width:130px;">Precio Unit.</th>
                                        <th class="text-right" style="width:120px;">Subtotal</th>
                                        <th>Nota</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($orden->detalles as $detalle)
                                    <tr>
                                        <td class="pl-3 font-weight-medium">
                                            @if($detalle->producto)
                                                {{ $detalle->producto->name }}
                                            @elseif($detalle->servicio)
                                                {{ $detalle->servicio->nombre_servicio }}
                                            @endif
                                        </td>
                                        <td>
                                            @if($detalle->producto)
                                                <span class="badge badge-info">Producto</span>
                                            @else
                                                <span class="badge badge-success">Servicio</span>
                                            @endif
                                        </td>
                                        <td class="text-center">{{ $detalle->cantidad }}</td>
                                        <td class="text-right">${{ number_format($detalle->precio_unitario, 0, ',', '.') }}</td>
                                        <td class="text-right font-weight-medium">${{ number_format($detalle->subtotal, 0, ',', '.') }}</td>
                                        <td class="text-muted small">{{ $detalle->nota }}</td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="6" class="text-center text-muted py-4">
                                            <i class="fas fa-inbox fa-2x mb-2 d-block"></i>
                                            Esta orden no tiene detalles registrados.
                                        </td>
                                    </tr>
                                    @endforelse
                                </tbody>
                                @if($orden->detalles->count() > 0)
                                <tfoot class="thead-light">
                                    <tr>
                                        <td colspan="3"></td>
                                        <td class="text-right font-weight-bold">Total:</td>
                                        <td class="text-right font-weight-bold text-success" style="font-size:1.05rem;">
                                            ${{ number_format($orden->costo_total, 0, ',', '.') }}
                                        </td>
                                        <td></td>
                                    </tr>
                                </tfoot>
                                @endif
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
    $('select[name="cliente_id"], select[name="artefacto_id"], select[name="tecnico_id"]').select2({ width: '100%' });

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
});
</script>
@endpush
@endsection
