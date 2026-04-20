@extends('layouts.app')

@section('content')
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Clientes</h1>
                </div>
                <div class="col-sm-6">
                    <a class="btn btn-primary float-right" href="{{ route('clientes.create') }}">
                        Agregar Nuevo
                    </a>
                </div>
            </div>
        </div>
    </section>

    <div class="content px-3">
        @include('flash-message')

        <div class="clearfix"></div>

        <div class="card">
            <div class="card-body p-0">
                <div class="table-responsive p-3">
                    <table class="table table-striped" id="clientes-table">
                        <thead>
                            <tr>
                                <th>RUT</th>
                                <th>Nombre</th>
                                <th>Email</th>
                                <th>Teléfono</th>
                                <th>Tipo Cliente</th>
                                <th>Estado</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                    </table>
                </div>
            </div>
        </div>
    </div>

@push('page_scripts')
    <script>
        $(document).ready(function () {
            $('#clientes-table').DataTable({
                serverSide: true,
                processing: true,
                ajax: {
                    url: '{{ route('clientes.datatables') }}',
                    type: 'GET'
                },
                columns: [
                    { data: 0, orderable: true },
                    { data: 1, orderable: true },
                    { data: 2, orderable: true },
                    { data: 3, orderable: false },
                    { data: 4, orderable: true },
                    { data: 5, orderable: true },
                    { data: 6, orderable: false }
                ],
                order: [[1, 'asc']],
                pageLength: 15,
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
        });
    </script>
@endpush
@endsection
