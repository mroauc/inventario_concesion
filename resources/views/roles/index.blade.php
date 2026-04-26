@extends('layouts.app')

@section('content')
<section class="content-header">
    <div class="container-fluid">
        <div class="row mb-2 align-items-center">
            <div class="col-sm-6">
                <h1><i class="fas fa-shield-alt text-brand mr-2"></i>Roles y Permisos</h1>
            </div>
        </div>
    </div>
</section>

<div class="content px-3">
    @include('flash::message')

    <div class="row">
        @foreach($roles as $rol)
        @php
            $badge = match($rol->name) {
                'super_admin'       => 'danger',
                'administrador'     => 'primary',
                'operador_servicio' => 'info',
                default             => 'secondary',
            };
            $label = match($rol->name) {
                'super_admin'       => 'Super Admin',
                'administrador'     => 'Administrador',
                'operador_servicio' => 'Operador de Servicio',
                default             => $rol->name,
            };
            $bloqueado = $rol->name === 'super_admin';
        @endphp
        <div class="col-md-4">
            <div class="card card-outline card-{{ $badge }} card-brand-top shadow-sm">
                <div class="card-header">
                    <h3 class="card-title">
                        <span class="badge badge-{{ $badge }} mr-2">{{ $label }}</span>
                    </h3>
                    <div class="card-tools">
                        @if(!$bloqueado)
                        <a href="{{ route('roles.show', $rol) }}" class="btn btn-sm btn-outline-brand">
                            <i class="fas fa-edit mr-1"></i> Editar permisos
                        </a>
                        @else
                        <span class="badge badge-secondary"><i class="fas fa-lock mr-1"></i> Protegido</span>
                        @endif
                    </div>
                </div>
                <div class="card-body p-0">
                    <ul class="list-group list-group-flush">
                        @forelse($rol->permissions->sortBy('name') as $permiso)
                        @php
                            [$modulo, $accion] = explode('.', $permiso->name) + [1 => ''];
                        @endphp
                        <li class="list-group-item py-1 px-3 d-flex justify-content-between align-items-center">
                            <small class="text-muted">{{ $modulo }}</small>
                            <span class="badge badge-light border">{{ $accion }}</span>
                        </li>
                        @empty
                        <li class="list-group-item text-muted text-center py-3">
                            <small>Sin permisos asignados</small>
                        </li>
                        @endforelse
                    </ul>
                </div>
                <div class="card-footer text-muted">
                    <small>{{ $rol->permissions->count() }} permiso(s) asignado(s)</small>
                </div>
            </div>
        </div>
        @endforeach
    </div>
</div>
@endsection
