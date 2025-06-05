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
                            <th>Fecha</th>
                            <th>Usuario</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($logs as $log)
                            <tr>
                                <td>{{$log->activity}}</td>
                                <td>{{$log->content}}</td>
                                <td>{{$log->user->name}}</td>
                                <td>{{$log->created_at}}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                
            </div>

        </div>
    </div>

@endsection

@push('page_scripts')
    <script>
        var tabla = $('#table_historial').DataTable({
            ordering: true,
            responsive: true,
            'order': [],
            'language': {
                "sProcessing":     "Procesando...",
                "sLengthMenu":     "Mostrar _MENU_ registros",
                "sZeroRecords":    "No se encontraron resultados",
                "sEmptyTable":     "Ningún dato disponible en esta tabla",
                "sInfo":           "Mostrando registros del _START_ al _END_ de un total de _TOTAL_ registros",
                "sInfoEmpty":      "Mostrando registros del 0 al 0 de un total de 0 registros",
                "sInfoFiltered":   "(filtrado de un total de _MAX_ registros)",
                "sInfoPostFix":    "",
                "sSearch":         "Buscar:",
                "sUrl":            "",
                "sInfoThousands":  ",",
                "sLoadingRecords": "Cargando...",
                "oPaginate": {
                    "sFirst":    "Primero",
                    "sLast":     "Último",
                    "sNext":     "Siguiente",
                    "sPrevious": "Anterior"
                },
                "oAria": {
                    "sSortAscending":  ": Activar para ordenar la columna de manera ascendente",
                    "sSortDescending": ": Activar para ordenar la columna de manera descendente"
                }
            }
        });
    </script>
@endpush