@extends('layouts.app')

@section('content')
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-12">
                    <h1>Editar Orden de Servicio #{{ $orden->numero }}</h1>
                </div>
            </div>
        </div>
    </section>

    <div class="content px-3">
        @include('flash-message')

        <form method="POST" action="{{ route('ordenes_servicio.update', $orden->id) }}">
            @csrf
            @method('PUT')
            
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Información de la Orden</h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="numero">Número de Orden</label>
                                <input type="text" class="form-control" value="#{{ $orden->numero }}" readonly disabled>
                                <small class="form-text text-muted">El número de orden no puede modificarse.</small>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="folio_garantia">Folio Garantía</label>
                                <input type="text" class="form-control" name="folio_garantia" value="{{ old('folio_garantia', $orden->folio_garantia) }}">
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="tipo_atencion">Tipo de Atención *</label>
                                <select class="form-control" name="tipo_atencion" required>
                                    <option value="taller" {{ $orden->tipo_atencion == 'taller' ? 'selected' : '' }}>Taller</option>
                                    <option value="terreno" {{ $orden->tipo_atencion == 'terreno' ? 'selected' : '' }}>Terreno</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="estado">Estado *</label>
                                <select class="form-control" name="estado" required>
                                    <option value="pendiente" {{ $orden->estado == 'pendiente' ? 'selected' : '' }}>Pendiente</option>
                                    <option value="en_progreso" {{ $orden->estado == 'en_progreso' ? 'selected' : '' }}>En Progreso</option>
                                    <option value="finalizada" {{ $orden->estado == 'finalizada' ? 'selected' : '' }}>Finalizada</option>
                                    <option value="cancelada" {{ $orden->estado == 'cancelada' ? 'selected' : '' }}>Cancelada</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="cliente_id">Cliente *</label>
                                <select class="form-control" name="cliente_id" required>
                                    @foreach($clientes as $cliente)
                                        <option value="{{ $cliente->id }}" {{ $orden->cliente_id == $cliente->id ? 'selected' : '' }}>
                                            {{ $cliente->rut ? $cliente->rut . ' - ' : '' }}{{ $cliente->nombre }} {{ $cliente->apellido }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="artefacto_id">Artefacto</label>
                                <select class="form-control" name="artefacto_id">
                                    <option value="">Seleccionar artefacto...</option>
                                    @foreach($artefactos as $artefacto)
                                        <option value="{{ $artefacto->id }}" {{ $orden->artefacto_id == $artefacto->id ? 'selected' : '' }}>
                                            {{ $artefacto->nombre }} - {{ $artefacto->marca }} {{ $artefacto->modelo }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="tecnico_id">Técnico</label>
                                <select class="form-control" name="tecnico_id">
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

                    <div class="row">
                        <div id="fecha-visita-container" class="col-md-6" style="{{ $orden->tipo_atencion == 'terreno' ? '' : 'display:none' }}">
                            <div class="form-group">
                                <label for="fecha_visita">Fecha de Visita</label>
                                <input type="datetime-local" class="form-control" name="fecha_visita"
                                       value="{{ old('fecha_visita', $orden->fecha_visita ? $orden->fecha_visita->format('Y-m-d\TH:i') : '') }}">
                            </div>
                        </div>
                        <div id="valor-visita-container" class="col-md-6" style="{{ $orden->tipo_atencion == 'terreno' ? '' : 'display:none' }}">
                            <div class="form-group">
                                <label for="valor_visita">Valor de Visita</label>
                                <input type="number" class="form-control" name="valor_visita" step="0.01" min="0"
                                       value="{{ old('valor_visita', $orden->valor_visita) }}" placeholder="0">
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="descripcion_falla">Descripción de la Falla *</label>
                        <textarea class="form-control" name="descripcion_falla" rows="3" required>{{ old('descripcion_falla', $orden->descripcion_falla) }}</textarea>
                    </div>

                    <div class="form-group">
                        <label for="observaciones">Observaciones</label>
                        <textarea class="form-control" name="observaciones" rows="2">{{ old('observaciones', $orden->observaciones) }}</textarea>
                    </div>
                </div>
            </div>

            @if($orden->detalles->count() > 0)
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Detalles del Servicio</h3>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Item</th>
                                        <th>Tipo</th>
                                        <th>Cantidad</th>
                                        <th>Precio Unitario</th>
                                        <th>Subtotal</th>
                                        <th>Nota</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($orden->detalles as $detalle)
                                        <tr>
                                            <td>
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
                                            <td>{{ $detalle->cantidad }}</td>
                                            <td>${{ number_format($detalle->precio_unitario, 0, ',', '.') }}</td>
                                            <td>${{ number_format($detalle->subtotal, 0, ',', '.') }}</td>
                                            <td>{{ $detalle->nota }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <th colspan="4">Total:</th>
                                        <th>${{ number_format($orden->costo_total, 0, ',', '.') }}</th>
                                        <th></th>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>
            @endif

            <div class="card">
                <div class="card-footer">
                    <button type="submit" class="btn btn-primary">Actualizar Orden</button>
                    <a href="{{ route('ordenes_servicio.show', $orden->id) }}" class="btn btn-secondary">Cancelar</a>
                </div>
            </div>
        </form>
    </div>
@push('page_scripts')
<script>
$(document).ready(function() {
    $('select[name="tipo_atencion"]').on('change', function() {
        if ($(this).val() === 'terreno') {
            $('#fecha-visita-container').show();
            $('#valor-visita-container').show();
        } else {
            $('#fecha-visita-container').hide();
            $('input[name="fecha_visita"]').val('');
            $('#valor-visita-container').hide();
            $('input[name="valor_visita"]').val('0');
        }
    });
});
</script>
@endpush
@endsection