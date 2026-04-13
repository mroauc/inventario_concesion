@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Detalle del Técnico</h3>
                    <div class="card-tools">
                        <a href="{{ route('tecnicos.edit', $tecnico) }}" class="btn btn-warning btn-sm">
                            <i class="fas fa-edit"></i> Editar
                        </a>
                        <a href="{{ route('tecnicos.index') }}" class="btn btn-secondary btn-sm">
                            <i class="fas fa-arrow-left"></i> Volver
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <table class="table table-borderless">
                                <tr>
                                    <th>ID:</th>
                                    <td>{{ $tecnico->id }}</td>
                                </tr>
                                <tr>
                                    <th>Usuario:</th>
                                    <td>{{ $tecnico->user->name ?? 'Sin asignar' }}</td>
                                </tr>
                                <tr>
                                    <th>Especialidad:</th>
                                    <td>{{ $tecnico->especialidad }}</td>
                                </tr>
                                <tr>
                                    <th>Teléfono:</th>
                                    <td>{{ $tecnico->telefono_contacto ?? 'No especificado' }}</td>
                                </tr>
                                <tr>
                                    <th>Email:</th>
                                    <td>{{ $tecnico->email_contacto ?? 'No especificado' }}</td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <table class="table table-borderless">
                                <tr>
                                    <th>Zona de Cobertura:</th>
                                    <td>{{ $tecnico->zona_cobertura ?? 'No especificada' }}</td>
                                </tr>
                                <tr>
                                    <th>Disponibilidad:</th>
                                    <td>
                                        <span class="badge badge-{{ $tecnico->disponibilidad == 'disponible' ? 'success' : ($tecnico->disponibilidad == 'ocupado' ? 'warning' : 'danger') }}">
                                            {{ ucfirst($tecnico->disponibilidad) }}
                                        </span>
                                    </td>
                                </tr>
                                <tr>
                                    <th>Creado:</th>
                                    <td>{{ $tecnico->created_at->format('d/m/Y H:i') }}</td>
                                </tr>
                                <tr>
                                    <th>Actualizado:</th>
                                    <td>{{ $tecnico->updated_at->format('d/m/Y H:i') }}</td>
                                </tr>
                            </table>
                        </div>
                    </div>
                    
                    @if($tecnico->certificaciones)
                    <div class="row mt-3">
                        <div class="col-md-12">
                            <h5>Certificaciones:</h5>
                            <p class="text-muted">{{ $tecnico->certificaciones }}</p>
                        </div>
                    </div>
                    @endif
                    
                    @if($tecnico->nota)
                    <div class="row mt-3">
                        <div class="col-md-12">
                            <h5>Nota:</h5>
                            <p class="text-muted">{{ $tecnico->nota }}</p>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection