@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Técnicos</h3>
                    <div class="card-tools">
                        <a href="{{ route('tecnicos.create') }}" class="btn btn-primary btn-sm">
                            <i class="fas fa-plus"></i> Nuevo Técnico
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Usuario</th>
                                    <th>Especialidad</th>
                                    <th>Teléfono</th>
                                    <th>Email</th>
                                    <th>Zona</th>
                                    <th>Disponibilidad</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($tecnicos as $tecnico)
                                <tr>
                                    <td>{{ $tecnico->id }}</td>
                                    <td>{{ $tecnico->user->name ?? 'Sin asignar' }}</td>
                                    <td>{{ $tecnico->especialidad }}</td>
                                    <td>{{ $tecnico->telefono_contacto }}</td>
                                    <td>{{ $tecnico->email_contacto }}</td>
                                    <td>{{ $tecnico->zona_cobertura }}</td>
                                    <td>
                                        <span class="badge badge-{{ $tecnico->disponibilidad == 'disponible' ? 'success' : ($tecnico->disponibilidad == 'ocupado' ? 'warning' : 'danger') }}">
                                            {{ ucfirst($tecnico->disponibilidad) }}
                                        </span>
                                    </td>
                                    <td>
                                        <a href="{{ route('tecnicos.show', $tecnico) }}" class="btn btn-info btn-sm">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="{{ route('tecnicos.edit', $tecnico) }}" class="btn btn-warning btn-sm">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <form action="{{ route('tecnicos.destroy', $tecnico) }}" method="POST" class="d-inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('¿Está seguro?')">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="8" class="text-center">No hay técnicos registrados</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="card-footer">
                    {{ $tecnicos->links() }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection