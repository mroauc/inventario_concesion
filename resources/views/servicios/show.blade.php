@extends('layouts.app')

@section('content')
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Detalles del Servicio</h1>
                </div>
                <div class="col-sm-6">
                    <a class="btn btn-default float-right" href="{{ route('servicios.index') }}">
                        Volver
                    </a>
                </div>
            </div>
        </div>
    </section>

    <div class="content px-3">
        <div class="card">
            <div class="card-body">
                <div class="row">
                    <div class="col-sm-6">
                        <div class="form-group">
                            <label>Nombre del Servicio:</label>
                            <p>{{ $servicio->nombre_servicio }}</p>
                        </div>
                    </div>

                    <div class="col-sm-6">
                        <div class="form-group">
                            <label>Precio:</label>
                            <p>${{ number_format($servicio->precio, 0, ',', '.') }}</p>
                        </div>
                    </div>

                    <div class="col-sm-6">
                        <div class="form-group">
                            <label>Duración Estimada:</label>
                            <p>{{ $servicio->duracion_estimada ? $servicio->duracion_estimada . ' minutos' : 'No especificada' }}</p>
                        </div>
                    </div>

                    <div class="col-sm-6">
                        <div class="form-group">
                            <label>Requiere Repuestos:</label>
                            <p>
                                <span class="badge badge-{{ $servicio->requiere_repuestos ? 'warning' : 'secondary' }}">
                                    {{ $servicio->requiere_repuestos ? 'Sí' : 'No' }}
                                </span>
                            </p>
                        </div>
                    </div>

                    <div class="col-sm-6">
                        <div class="form-group">
                            <label>Estado:</label>
                            <p>
                                <span class="badge badge-{{ $servicio->estado ? 'success' : 'danger' }}">
                                    {{ $servicio->estado ? 'Activo' : 'Inactivo' }}
                                </span>
                            </p>
                        </div>
                    </div>

                    @if($servicio->descripcion)
                    <div class="col-sm-12">
                        <div class="form-group">
                            <label>Descripción:</label>
                            <p>{{ $servicio->descripcion }}</p>
                        </div>
                    </div>
                    @endif
                </div>
            </div>

            <div class="card-footer">
                <a href="{{ route('servicios.edit', $servicio->id) }}" class="btn btn-primary">Editar</a>
                <a href="{{ route('servicios.index') }}" class="btn btn-default">Volver</a>
            </div>
        </div>
    </div>
@endsection