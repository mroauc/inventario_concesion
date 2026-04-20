@extends('layouts.app')

@section('content')
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Historial</h1>
                </div>
            </div>
        </div>
    </section>

    <div class="content px-3">

        @include('flash::message')

        <div class="clearfix"></div>

        <div class="card">
            <div class="card-body p-4">

                <table id="table_historial" class="table table-sm table-bordered">
                    <thead>
                        <tr style="background-color: #efefef">
                            <th>Actividad</th>
                            <th>Contenido</th>
                            <th>Usuario</th>
                            <th>Fecha</th>
                        </tr>
                    </thead>
                </table>

            </div>
        </div>
    </div>

@endsection

@push('page_scripts')
    <script>
        $('#table_historial').DataTable({
            serverSide: true,
            processing: true,
            ajax: {
                url: '{{ route('logs.datatables') }}',
                type: 'GET'
            },
            columns: [
                { data: 0, orderable: false },
                { data: 1, orderable: false },
                { data: 2, orderable: false },
                { data: 3, orderable: true }
            ],
            order: [[3, 'desc']],
            pageLength: 25,
            language: {
                "sProcessing":   "Procesando...",
                "sLengthMenu":   "Mostrar _MENU_ registros",
                "sZeroRecords":  "No se encontraron resultados",
                "sEmptyTable":   "Ningún dato disponible en esta tabla",
                "sInfo":         "Mostrando registros del _START_ al _END_ de un total de _TOTAL_ registros",
                "sInfoEmpty":    "Mostrando registros del 0 al 0 de un total de 0 registros",
                "sInfoFiltered": "(filtrado de un total de _MAX_ registros)",
                "sSearch":       "Buscar:",
                "sLoadingRecords": "Cargando...",
                "oPaginate": {
                    "sFirst":    "Primero",
                    "sLast":     "Último",
                    "sNext":     "Siguiente",
                    "sPrevious": "Anterior"
                }
            }
        });
    </script>
@endpush
