@extends('layouts.app')

@section('content')
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-12">
                    <h1>Crear Oferta</h1>
                </div>
            </div>
        </div>
    </section>

    <div class="content px-3">
        @include('common.errors')

        <div class="card card-outline card-primary card-brand-top shadow-sm">
            <form method="POST" action="{{ route('ofertas.store') }}" enctype="multipart/form-data">
                @csrf
                <div class="card-body">
                    <div class="row">
                        @include('ofertas.fields')
                    </div>
                </div>

                <div class="card-footer">
                    <button type="submit" class="btn btn-brand">Guardar</button>
                    <a href="{{ route('ofertas.index') }}" class="btn btn-default">Cancelar</a>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('page_css')
    @include('ofertas._slots_css')
@endpush

@push('page_scripts')
    @include('ofertas._preview_script')
@endpush
