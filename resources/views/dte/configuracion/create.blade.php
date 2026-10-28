@extends('layouts.app')

@section('content')
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-12">
                    <h1>Configuracion Fiscal</h1>
                </div>
            </div>
        </div>
    </section>

    <div class="content px-3">
        @include('adminlte-templates::common.errors')

        <div class="card card-brand-top">
            <form action="{{ route('dte.configuracion.store') }}" method="POST">
                @csrf
                <div class="card-body">
                    @include('dte.configuracion.fields')
                </div>
                <div class="card-footer">
                    <button type="submit" class="btn btn-brand">Guardar</button>
                    <a href="{{ route('home') }}" class="btn btn-default">Cancelar</a>
                </div>
            </form>
        </div>
    </div>
@endsection
