@extends('layouts.app')

@section('content')
<section class="content-header">
    <div class="container-fluid">
        <div class="row mb-2 align-items-center">
            <div class="col-sm-6">
                <h1><i class="fas fa-shield-alt text-brand mr-2"></i>Editar Permisos del Rol</h1>
            </div>
            <div class="col-sm-6">
                <a href="{{ route('roles.index') }}" class="btn btn-outline-secondary float-right">
                    <i class="fas fa-arrow-left mr-1"></i> Volver
                </a>
            </div>
        </div>
    </div>
</section>

<div class="content px-3">
    @include('flash::message')
    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    @php
        $label = match($rol->name) {
            'super_admin'       => 'Super Admin',
            'administrador'     => 'Administrador',
            'operador_servicio' => 'Operador de Servicio',
            default             => $rol->name,
        };
        $badge = match($rol->name) {
            'super_admin'       => 'danger',
            'administrador'     => 'primary',
            'operador_servicio' => 'info',
            default             => 'secondary',
        };
        $permisosActuales = $rol->permissions->pluck('name')->toArray();

        $etiquetasModulo = [
            'productos'       => ['icon' => 'fa-box',              'color' => 'success'],
            'bodegas'         => ['icon' => 'fa-warehouse',         'color' => 'success'],
            'categorias'      => ['icon' => 'fa-tags',              'color' => 'success'],
            'artefactos'      => ['icon' => 'fa-blender',           'color' => 'info'],
            'tipo_artefactos' => ['icon' => 'fa-th-list',           'color' => 'info'],
            'ordenes'         => ['icon' => 'fa-clipboard-list',    'color' => 'warning'],
            'clientes'        => ['icon' => 'fa-users',             'color' => 'warning'],
            'tecnicos'        => ['icon' => 'fa-hard-hat',          'color' => 'warning'],
            'servicios'       => ['icon' => 'fa-wrench',            'color' => 'warning'],
            'flujo_caja'      => ['icon' => 'fa-cash-register',     'color' => 'primary'],
            'historial'       => ['icon' => 'fa-history',           'color' => 'secondary'],
            'usuarios'        => ['icon' => 'fa-users-cog',         'color' => 'danger'],
            'concesiones'     => ['icon' => 'fa-building',          'color' => 'danger'],
            'representantes'  => ['icon' => 'fa-user-tie',          'color' => 'danger'],
        ];
    @endphp

    <form action="{{ route('roles.update', $rol) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="card card-outline card-{{ $badge }} card-brand-top shadow-sm mb-3">
            <div class="card-header">
                <h3 class="card-title">
                    <span class="badge badge-{{ $badge }} mr-2">{{ $label }}</span>
                    Selecciona los permisos que tendrá este rol
                </h3>
                <div class="card-tools">
                    <button type="button" class="btn btn-sm btn-outline-secondary" id="btn-marcar-todos">
                        <i class="fas fa-check-double mr-1"></i> Marcar todos
                    </button>
                    <button type="button" class="btn btn-sm btn-outline-secondary ml-1" id="btn-desmarcar-todos">
                        <i class="fas fa-times mr-1"></i> Desmarcar todos
                    </button>
                </div>
            </div>
        </div>

        <div class="row">
            @foreach($todosLosPermisos as $modulo => $permisos)
            @php
                $meta = $etiquetasModulo[$modulo] ?? ['icon' => 'fa-key', 'color' => 'secondary'];
            @endphp
            <div class="col-md-4 col-sm-6">
                <div class="card card-outline card-{{ $meta['color'] }} shadow-sm mb-3">
                    <div class="card-header py-2">
                        <h3 class="card-title">
                            <i class="fas {{ $meta['icon'] }} mr-2 text-{{ $meta['color'] }}"></i>
                            {{ ucfirst(str_replace('_', ' ', $modulo)) }}
                        </h3>
                        <div class="card-tools">
                            <button type="button" class="btn btn-xs btn-outline-secondary btn-marcar-modulo"
                                    data-modulo="{{ $modulo }}">
                                Todos
                            </button>
                        </div>
                    </div>
                    <div class="card-body py-2">
                        @foreach($permisos as $permiso)
                        @php
                            $accion = explode('.', $permiso->name)[1] ?? $permiso->name;
                            $checked = in_array($permiso->name, $permisosActuales);
                        @endphp
                        <div class="form-check mb-1">
                            <input class="form-check-input permiso-check modulo-{{ $modulo }}"
                                   type="checkbox"
                                   name="permisos[]"
                                   value="{{ $permiso->name }}"
                                   id="perm-{{ $permiso->id }}"
                                   {{ $checked ? 'checked' : '' }}>
                            <label class="form-check-label" for="perm-{{ $permiso->id }}">
                                <code class="text-{{ $meta['color'] }}">{{ $accion }}</code>
                            </label>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
            @endforeach
        </div>

        <div class="d-flex justify-content-end mb-4">
            <a href="{{ route('roles.index') }}" class="btn btn-outline-brand mr-2">Cancelar</a>
            <button type="submit" class="btn btn-brand">
                <i class="fas fa-save mr-1"></i> Guardar cambios
            </button>
        </div>
    </form>
</div>
@endsection

@push('page_scripts')
<script>
$(document).ready(function () {

    $('#btn-marcar-todos').on('click', function () {
        $('.permiso-check').prop('checked', true);
    });

    $('#btn-desmarcar-todos').on('click', function () {
        $('.permiso-check').prop('checked', false);
    });

    $('.btn-marcar-modulo').on('click', function () {
        var modulo = $(this).data('modulo');
        var checks = $('.modulo-' + modulo);
        var todosChecked = checks.filter(':checked').length === checks.length;
        checks.prop('checked', !todosChecked);
    });

});
</script>
@endpush
