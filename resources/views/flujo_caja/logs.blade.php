@extends('layouts.app')

@section('content')
<section class="content-header">
    <div class="container-fluid">
        <div class="row mb-2 align-items-center">
            <div class="col-sm-6">
                <h1><i class="fas fa-history text-brand mr-2"></i>Historial Flujo de Caja</h1>
            </div>
            <div class="col-sm-6 d-flex justify-content-end">
                <a href="{{ route('flujo_caja.index') }}" class="btn btn-outline-brand">
                    <i class="fas fa-arrow-left mr-1"></i> Volver a Flujo de Caja
                </a>
            </div>
        </div>
    </div>
</section>

<div class="content px-3">
    <div class="card card-outline card-secondary shadow-sm">
        <div class="card-body p-0">
            <table id="tabla-logs-caja" class="table table-sm table-hover mb-0">
                <thead class="thead-light">
                    <tr>
                        <th style="width:120px">Actividad</th>
                        <th>Contenido</th>
                        <th style="width:150px">Usuario</th>
                        <th style="width:140px">Fecha</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>
</div>
@endsection

@push('page_scripts')
<script>
$('#tabla-logs-caja').DataTable({
    serverSide: true,
    processing: true,
    ajax: {
        url: '{{ route('flujo_caja.logs.datatables') }}',
        type: 'GET'
    },
    columns: [
        { data: 'activity', orderable: false },
        { data: 'content',  orderable: false },
        { data: 'user',     orderable: false },
        { data: 'created_at', orderable: true }
    ],
    order: [[3, 'desc']],
    pageLength: 25,
    language: {
        "sProcessing":   "Procesando...",
        "sLengthMenu":   "Mostrar _MENU_ registros",
        "sZeroRecords":  "No se encontraron resultados",
        "sEmptyTable":   "Sin registros de historial aún.",
        "sInfo":         "Mostrando del _START_ al _END_ de _TOTAL_ registros",
        "sInfoEmpty":    "Mostrando 0 registros",
        "sInfoFiltered": "(filtrado de _MAX_ totales)",
        "sSearch":       "Buscar:",
        "sLoadingRecords": "Cargando...",
        "oPaginate": {
            "sFirst": "Primero", "sLast": "Último",
            "sNext": "Siguiente", "sPrevious": "Anterior"
        }
    },
    columnDefs: [
        {
            targets: 0,
            render: function(data) {
                var map = {
                    'Creación':   'badge-success',
                    'Edición':    'badge-primary',
                    'Eliminación':'badge-danger'
                };
                var cls = map[data] || 'badge-secondary';
                return '<span class="badge ' + cls + '">' + data + '</span>';
            }
        }
    ]
});
</script>
@endpush
