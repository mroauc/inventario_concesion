@extends('layouts.app')

@section('content')
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Emitir Boleta Electrónica</h1>
                </div>
            </div>
        </div>
    </section>

    <div class="content px-3">
        @include('flash::message')

        <form action="{{ route('dte.boletas.store') }}" method="POST" id="formBoleta">
            @csrf

            @if($orden)
                <input type="hidden" name="orden_servicio_id" value="{{ $orden->id }}">

                <div class="card card-brand-top">
                    <div class="card-header">
                        <h3 class="card-title">Orden de Servicio #{{ $orden->numero }}</h3>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <strong>Cliente:</strong>
                                {{ $orden->cliente->nombre }} {{ $orden->cliente->apellido ?? '' }}<br>
                                @if($orden->cliente->rut)
                                    <strong>RUT:</strong> {{ $orden->cliente->rut }}<br>
                                @else
                                    <span class="text-warning"><i class="fas fa-exclamation-triangle"></i> Sin RUT — se emitirá como consumidor final (66666666-6)</span><br>
                                @endif
                                @if($orden->cliente->direccion)
                                    <strong>Dirección:</strong> {{ $orden->cliente->direccion }}<br>
                                @endif
                            </div>
                            <div class="col-md-6">
                                <strong>Fecha:</strong> {{ $orden->fecha_orden->format('d/m/Y') }}<br>
                                <strong>Estado:</strong> {{ ucfirst($orden->estado) }}<br>
                                <strong>Total Orden:</strong> ${{ number_format($orden->costo_total, 0, ',', '.') }}
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card card-brand-top">
                    <div class="card-header">
                        <h3 class="card-title">Detalle de la Boleta</h3>
                    </div>
                    <div class="card-body p-0">
                        <table class="table" id="tablaItems">
                            <thead>
                                <tr>
                                    <th>Descripción</th>
                                    <th style="width:100px">Cant.</th>
                                    <th style="width:150px">Precio Unit.</th>
                                    <th style="width:130px" class="text-right">Subtotal</th>
                                    <th style="width:50px"></th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($orden->detalles as $i => $detalle)
                                <tr>
                                    <td>
                                        <input type="text" name="items[{{ $i }}][nombre]" class="form-control form-control-sm"
                                               value="{{ $detalle->producto ? $detalle->producto->name : ($detalle->servicio ? $detalle->servicio->nombre_servicio : '') }}" required>
                                        <input type="hidden" name="items[{{ $i }}][producto_id]" value="{{ $detalle->producto_id }}">
                                        <input type="hidden" name="items[{{ $i }}][servicio_id]" value="{{ $detalle->servicio_id }}">
                                    </td>
                                    <td>
                                        <input type="number" name="items[{{ $i }}][cantidad]" class="form-control form-control-sm item-cantidad"
                                               value="{{ $detalle->cantidad }}" min="0.01" step="0.01" required>
                                    </td>
                                    <td>
                                        <input type="number" name="items[{{ $i }}][precio_unitario]" class="form-control form-control-sm item-precio"
                                               value="{{ (int)$detalle->precio_unitario }}" min="1" required>
                                    </td>
                                    <td class="text-right align-middle item-subtotal">
                                        ${{ number_format($detalle->subtotal, 0, ',', '.') }}
                                    </td>
                                    <td>
                                        <button type="button" class="btn btn-sm btn-outline-danger btn-remove-item" title="Quitar">
                                            <i class="fas fa-times"></i>
                                        </button>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                            <tfoot>
                                <tr>
                                    <td colspan="3" class="text-right"><strong>Total (IVA incluido):</strong></td>
                                    <td class="text-right"><strong id="totalBoleta">$0</strong></td>
                                    <td></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            @else
                <div class="alert alert-info">
                    <i class="fas fa-info-circle"></i>
                    Para emitir una boleta, primero seleccione una orden de servicio desde el
                    <a href="{{ route('ordenes_servicio.index') }}">listado de órdenes</a>.
                </div>
            @endif

            @if($orden)
            <div class="row">
                <div class="col-12">
                    <button type="submit" class="btn btn-brand" id="btnEmitir">
                        <i class="fas fa-file-invoice mr-1"></i> Emitir Boleta
                    </button>
                    <a href="{{ route('ordenes_servicio.show', $orden->id) }}" class="btn btn-secondary ml-2">
                        Cancelar
                    </a>
                </div>
            </div>
            @endif
        </form>
    </div>
@endsection

@if($orden)
@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    function recalcular() {
        var total = 0;
        document.querySelectorAll('#tablaItems tbody tr').forEach(function(row) {
            var cant = parseFloat(row.querySelector('.item-cantidad')?.value) || 0;
            var precio = parseInt(row.querySelector('.item-precio')?.value) || 0;
            var subtotal = Math.round(cant * precio);
            var subtotalCell = row.querySelector('.item-subtotal');
            if (subtotalCell) {
                subtotalCell.textContent = '$' + subtotal.toLocaleString('es-CL');
            }
            total += subtotal;
        });
        document.getElementById('totalBoleta').textContent = '$' + total.toLocaleString('es-CL');
    }

    document.querySelectorAll('.item-cantidad, .item-precio').forEach(function(input) {
        input.addEventListener('input', recalcular);
    });

    document.querySelectorAll('.btn-remove-item').forEach(function(btn) {
        btn.addEventListener('click', function() {
            if (document.querySelectorAll('#tablaItems tbody tr').length > 1) {
                this.closest('tr').remove();
                recalcular();
            }
        });
    });

    document.getElementById('formBoleta').addEventListener('submit', function(e) {
        var btn = document.getElementById('btnEmitir');
        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin mr-1"></i> Emitiendo...';
    });

    recalcular();
});
</script>
@endpush
@endif
