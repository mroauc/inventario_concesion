@extends('layouts.app')

@section('content')
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Tipos de Artefacto</h1>
                </div>
                <div class="col-sm-6">
                    <a class="btn btn-brand float-right" href="{{ route('tipo_artefactos.create') }}">
                        <i class="fas fa-plus mr-1"></i> Agregar Nuevo
                    </a>
                </div>
            </div>
        </div>
    </section>

    <div class="content px-3">
        @include('flash-message')

        <div class="card card-outline card-primary card-brand-top shadow-sm">
            <div class="card-body p-0">
                <div class="row p-3">
                    <div class="col-md-6">
                        <form method="GET" action="{{ route('tipo_artefactos.index') }}">
                            <div class="input-group">
                                <input type="text" name="search" class="form-control"
                                       placeholder="Buscar tipos..." value="{{ request('search') }}">
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
                    <table class="table table-striped" id="tipo-artefactos-table">
                        <thead>
                            <tr>
                                <th>Nombre</th>
                                <th>Descripción</th>
                                <th>Artefactos</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                        @forelse($tipos as $tipo)
                            <tr>
                                <td>{{ $tipo->nombre }}</td>
                                <td>{{ $tipo->descripcion ?? '—' }}</td>
                                <td>{{ $tipo->artefactos()->count() }}</td>
                                <td>
                                    <div class="btn-group">
                                        <a href="{{ route('tipo_artefactos.edit', $tipo->id) }}"
                                           class="btn btn-default btn-xs">
                                            <i class="far fa-edit"></i>
                                        </a>
                                        <form method="POST"
                                              action="{{ route('tipo_artefactos.destroy', $tipo->id) }}"
                                              style="display: inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-danger btn-xs"
                                                    onclick="return confirm('¿Está seguro de eliminar este tipo?')">
                                                <i class="far fa-trash-alt"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center text-muted">No hay tipos de artefacto registrados.</td>
                            </tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="card-footer clearfix">
                    <div class="float-right">
                        {{ $tipos->appends(request()->query())->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
