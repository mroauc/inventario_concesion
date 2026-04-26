@extends('layouts.app')

@section('content')
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Editar Usuario</h1>
                </div>
                <div class="col-sm-6">
                    <a href="{{ route('users.index') }}" class="btn btn-outline-secondary float-right">
                        <i class="fas fa-arrow-left mr-1"></i> Volver
                    </a>
                </div>
            </div>
        </div>
    </section>

    <div class="content px-3">
        @include('adminlte-templates::common.errors')

        <div class="card card-outline card-primary card-brand-top shadow-sm">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-user-edit mr-2 text-brand"></i>{{ $user->name }}
                </h3>
            </div>
            {!! Form::model($user, ['route' => ['users.update', $user->id], 'method' => 'patch']) !!}
            <div class="card-body">
                <div class="row">
                    @include('users.fields')
                </div>
            </div>
            <div class="card-footer">
                <button type="submit" class="btn btn-brand">
                    <i class="fas fa-save mr-1"></i> Actualizar
                </button>
                <a href="{{ route('users.index') }}" class="btn btn-outline-brand ml-2">Cancelar</a>
            </div>
            {!! Form::close() !!}
        </div>
    </div>
@endsection
