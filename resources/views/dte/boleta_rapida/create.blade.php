@extends('layouts.app')

@section('content')
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1><i class="fas fa-bolt text-warning"></i> Venta Rapida — Boleta Electronica</h1>
                </div>
                <div class="col-sm-6 text-right">
                    <a href="{{ route('dte.boletas.index') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Volver a Boletas
                    </a>
                </div>
            </div>
        </div>
    </section>

    <div class="content px-3">
        @include('flash::message')

        <form action="{{ route('dte.boleta_rapida.store') }}" method="POST" id="formVentaRapida">
            @csrf

            <div class="row">
                <div class="col-md-8">
                    <div class="card card-brand-top">
                        <div class="card-header d-flex align-items-center">
                            <h3 class="card-title">Items de Venta</h3>
                            <button type="button" class="btn btn-sm btn-outline-success ml-auto" id="btnAgregarItem">
                                <i class="fas fa-plus"></i> Agregar Item
                            </button>
                        </div>
                        <div class="card-body p-0">
                            <table class="table" id="tablaItems">
                                <thead>
                                    <tr>
                                        <th>Descripcion</th>
                                        <th style="width:90px">Cant.</th>
                                        <th style="width:140px">Precio Unit.</th>
                                        <th style="width:130px" class="text-right">Subtotal</th>
                                        <th style="width:50px"></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr data-idx="0">
                                        <td>
                                            <input type="text" name="items[0][nombre]" class="form-control form-control-sm"
                                                   placeholder="Nombre del producto o servicio" required>
                                        </td>
                                        <td>
                                            <input type="number" name="items[0][cantidad]" class="form-control form-control-sm item-cantidad"
                                                   value="1" min="0.01" step="0.01" required>
                                        </td>
                                        <td>
                                            <input type="number" name="items[0][precio_unitario]" class="form-control form-control-sm item-precio"
                                                   placeholder="$" min="1" required>
                                        </td>
                                        <td class="text-right align-middle item-subtotal">$0</td>
                                        <td>
                                            <button type="button" class="btn btn-sm btn-outline-danger btn-remove-item" title="Quitar">
                                                <i class="fas fa-times"></i>
                                            </button>
                                        </td>
                                    </tr>
                                </tbody>
                                <tfoot>
                                    <tr class="bg-light">
                                        <td colspan="3" class="text-right"><strong>Total (IVA incluido):</strong></td>
                                        <td class="text-right"><strong id="totalBoleta" style="font-size:1.2em">$0</strong></td>
                                        <td></td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="card card-brand-top">
                        <div class="card-header">
                            <h3 class="card-title">Receptor (opcional)</h3>
                        </div>
                        <div class="card-body">
                            <p class="text-muted small mb-3">
                                Si no indica datos se emite como consumidor final (66666666-6).
                            </p>
                            <div class="form-group">
                                <label>RUT</label>
                                <input type="text" name="receptor_rut" class="form-control form-control-sm"
                                       placeholder="12345678-9" value="{{ old('receptor_rut') }}">
                            </div>
                            <div class="form-group">
                                <label>Nombre / Razon Social</label>
                                <input type="text" name="receptor_nombre" class="form-control form-control-sm"
                                       placeholder="Juan Perez" value="{{ old('receptor_nombre') }}">
                            </div>
                        </div>
                    </div>

                    <div class="card bg-light">
                        <div class="card-body text-center">
                            <p class="mb-2"><i class="fas fa-info-circle text-primary"></i> <strong>Envio sincrono</strong></p>
                            <p class="small text-muted mb-0">
                                La boleta se enviara al SII en tiempo real.<br>
                                Al confirmar, el PDF estara listo para imprimir.
                            </p>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-brand btn-block btn-lg" id="btnEmitir">
                        <i class="fas fa-bolt mr-1"></i> Emitir Boleta
                    </button>
                </div>
            </div>
        </form>
    </div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    var itemIdx = 1;

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

    function bindRow(row) {
        row.querySelectorAll('.item-cantidad, .item-precio').forEach(function(input) {
            input.addEventListener('input', recalcular);
        });
        row.querySelector('.btn-remove-item')?.addEventListener('click', function() {
            if (document.querySelectorAll('#tablaItems tbody tr').length > 1) {
                row.remove();
                recalcular();
            }
        });
    }

    document.querySelectorAll('#tablaItems tbody tr').forEach(bindRow);

    document.getElementById('btnAgregarItem').addEventListener('click', function() {
        var tbody = document.querySelector('#tablaItems tbody');
        var tr = document.createElement('tr');
        tr.setAttribute('data-idx', itemIdx);
        tr.innerHTML =
            '<td><input type="text" name="items[' + itemIdx + '][nombre]" class="form-control form-control-sm" placeholder="Nombre del producto o servicio" required></td>' +
            '<td><input type="number" name="items[' + itemIdx + '][cantidad]" class="form-control form-control-sm item-cantidad" value="1" min="0.01" step="0.01" required></td>' +
            '<td><input type="number" name="items[' + itemIdx + '][precio_unitario]" class="form-control form-control-sm item-precio" placeholder="$" min="1" required></td>' +
            '<td class="text-right align-middle item-subtotal">$0</td>' +
            '<td><button type="button" class="btn btn-sm btn-outline-danger btn-remove-item" title="Quitar"><i class="fas fa-times"></i></button></td>';
        tbody.appendChild(tr);
        bindRow(tr);
        tr.querySelector('input[name*="[nombre]"]').focus();
        itemIdx++;
    });

    document.getElementById('formVentaRapida').addEventListener('submit', function(e) {
        var btn = document.getElementById('btnEmitir');
        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin mr-1"></i> Emitiendo y enviando al SII...';
    });

    recalcular();
});
</script>
@endpush
