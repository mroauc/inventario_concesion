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
                <div class="row p-3">
                    <div class="col-md-6">
                        <form method="GET" action="{{ route('clientes.index') }}">
                            <div class="input-group">
                                <input type="text" name="search" class="form-control" placeholder="Buscar clientes..." value="{{ request('search') }}">
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
                    <table class="table table-striped" id="clientes-table">
                        <thead>
                            <tr>
                                <th>Nombre</th>
                                <th>Apellido</th>
                                <th>Email</th>
                                <th>Teléfono</th>
                                <th>Tipo Cliente</th>
                                <th>Estado</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                        @foreach($clientes as $cliente)
                            <tr>
                                <td>{{ $cliente->nombre }}</td>
                                <td>{{ $cliente->apellido }}</td>
                                <td>{{ $cliente->email }}</td>
                                <td>{{ $cliente->numero_contacto }}</td>
                                <td>
                                    <span class="badge badge-{{ $cliente->tipo_cliente == 'empresa' ? 'primary' : ($cliente->tipo_cliente == 'concesion' ? 'success' : 'secondary') }}">
                                        {{ ucfirst($cliente->tipo_cliente) }}
                                    </span>
                                </td>
                                <td>
                                    <span class="badge badge-{{ $cliente->estado ? 'success' : 'danger' }}">
                                        {{ $cliente->estado ? 'Activo' : 'Inactivo' }}
                                    </span>
                                </td>
                                <td>
                                    <div class='btn-group'>
                                        <a href="{{ route('clientes.show', [$cliente->id]) }}" class='btn btn-default btn-xs'>
                                            <i class="far fa-eye"></i>
                                        </a>
                                        <a href="{{ route('clientes.edit', [$cliente->id]) }}" class='btn btn-default btn-xs'>
                                            <i class="far fa-edit"></i>
                                        </a>
                                        <form method="POST" action="{{ route('clientes.destroy', [$cliente->id]) }}" style="display: inline">
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
                        {{ $clientes->appends(request()->query())->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection