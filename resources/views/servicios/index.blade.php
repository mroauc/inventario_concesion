@extends('layouts.app')

@section('content')
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Servicios</h1>
                </div>
                <div class="col-sm-6">
                    <a class="btn btn-primary float-right" href="{{ route('servicios.create') }}">
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
                <div class="row p-3">
                    <div class="col-md-6">
                        <form method="GET" action="{{ route('servicios.index') }}">
                            <div class="input-group">
                                <input type="text" name="search" class="form-control" placeholder="Buscar servicios..." value="{{ request('search') }}">
                                <div class="input-group-append">
                                    <button class="btn btn-outline-secondary" type="submit">
                                        <i class="fa fa-search"></i>
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-striped" id="servicios-table">
                        <thead>
                            <tr>
                                <th>Nombre Servicio</th>
                                <th>Precio</th>
                                <th>Duración (min)</th>
                                <th>Requiere Repuestos</th>
                                <th>Estado</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                        @foreach($servicios as $servicio)
                            <tr>
                                <td>{{ $servicio->nombre_servicio }}</td>
                                <td>${{ number_format($servicio->precio, 0, ',', '.') }}</td>
                                <td>{{ $servicio->duracion_estimada ?? 'No especificada' }}</td>
                                <td>
                                    <span class="badge badge-{{ $servicio->requiere_repuestos ? 'warning' : 'secondary' }}">
                                        {{ $servicio->requiere_repuestos ? 'Sí' : 'No' }}
                                    </span>
                                </td>
                                <td>
                                    <span class="badge badge-{{ $servicio->estado ? 'success' : 'danger' }}">
                                        {{ $servicio->estado ? 'Activo' : 'Inactivo' }}
                                    </span>
                                </td>
                                <td>
                                    <div class='btn-group'>
                                        <a href="{{ route('servicios.show', [$servicio->id]) }}" class='btn btn-default btn-xs'>
                                            <i class="far fa-eye"></i>
                                        </a>
                                        <a href="{{ route('servicios.edit', [$servicio->id]) }}" class='btn btn-default btn-xs'>
                                            <i class="far fa-edit"></i>
                                        </a>
                                        <form method="POST" action="{{ route('servicios.destroy', [$servicio->id]) }}" style="display: inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class='btn btn-danger btn-xs' onclick="return confirm('¿Está seguro?')">
                                                <i class="far fa-trash-alt"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="card-footer clearfix">
                    <div class="float-right">
                        {{ $servicios->appends(request()->query())->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection