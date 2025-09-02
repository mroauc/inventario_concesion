@extends('layouts.app')

@section('content')
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Detalles del Cliente</h1>
                </div>
                <div class="col-sm-6">
                    <a class="btn btn-default float-right" href="{{ route('clientes.index') }}">
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
                            <label>Nombre:</label>
                            <p>{{ $cliente->nombre }}</p>
                        </div>
                    </div>

                    <div class="col-sm-6">
                        <div class="form-group">
                            <label>Apellido:</label>
                            <p>{{ $cliente->apellido }}</p>
                        </div>
                    </div>

                    <div class="col-sm-6">
                        <div class="form-group">
                            <label>Email:</label>
                            <p>{{ $cliente->email ?? 'No especificado' }}</p>
                        </div>
                    </div>

                    <div class="col-sm-6">
                        <div class="form-group">
                            <label>Teléfono:</label>
                            <p>{{ $cliente->numero_contacto }}</p>
                        </div>
                    </div>

                    <div class="col-sm-6">
                        <div class="form-group">
                            <label>Tipo Cliente:</label>
                            <p>
                                <span class="badge badge-{{ $cliente->tipo_cliente == 'empresa' ? 'primary' : ($cliente->tipo_cliente == 'concesion' ? 'success' : 'secondary') }}">
                                    {{ ucfirst($cliente->tipo_cliente) }}
                                </span>
                            </p>
                        </div>
                    </div>

                    <div class="col-sm-6">
                        <div class="form-group">
                            <label>RUT:</label>
                            <p>{{ $cliente->rut ?? 'No especificado' }}</p>
                        </div>
                    </div>

                    <div class="col-sm-12">
                        <div class="form-group">
                            <label>Dirección:</label>
                            <p>{{ $cliente->direccion }}</p>
                        </div>
                    </div>

                    <div class="col-sm-6">
                        <div class="form-group">
                            <label>Coordenadas:</label>
                            <p>{{ $cliente->coordenadas ?? 'No especificadas' }}</p>
                        </div>
                    </div>

                    <div class="col-sm-6">
                        <div class="form-group">
                            <label>Estado:</label>
                            <p>
                                <span class="badge badge-{{ $cliente->estado ? 'success' : 'danger' }}">
                                    {{ $cliente->estado ? 'Activo' : 'Inactivo' }}
                                </span>
                            </p>
                        </div>
                    </div>

                    @if($cliente->nota)
                    <div class="col-sm-12">
                        <div class="form-group">
                            <label>Nota:</label>
                            <p>{{ $cliente->nota }}</p>
                        </div>
                    </div>
                    @endif
                </div>
            </div>

            <div class="card-footer">
                <a href="{{ route('clientes.edit', $cliente->id) }}" class="btn btn-primary">Editar</a>
                <a href="{{ route('clientes.index') }}" class="btn btn-default">Volver</a>
            </div>
        </div>
    </div>
@endsection