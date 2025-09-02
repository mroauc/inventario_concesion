@extends('layouts.app')

@section('content')
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-12">
                    <h1>Editar Servicio</h1>
                </div>
            </div>
        </div>
    </section>

    <div class="content px-3">
        @include('common.errors')

        <div class="card">
            <form method="POST" action="{{ route('servicios.update', $servicio->id) }}">
                @csrf
                @method('PUT')
                <div class="card-body">
                    <div class="row">
                        @include('servicios.fields')
                    </div>
                </div>

                <div class="card-footer">
                    <button type="submit" class="btn btn-primary">Actualizar</button>
                    <a href="{{ route('servicios.index') }}" class="btn btn-default">Cancelar</a>
                </div>
            </form>
        </div>
    </div>
@endsection