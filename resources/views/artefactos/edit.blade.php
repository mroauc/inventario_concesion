@extends('layouts.app')

@section('content')
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-12">
                    <h1>Editar Artefacto</h1>
                </div>
            </div>
        </div>
    </section>

    <div class="content px-3">
        @include('common.errors')

        <div class="card card-outline card-primary card-brand-top shadow-sm">
            <form method="POST" action="{{ route('artefactos.update', $artefacto->id) }}">
                @csrf
                @method('PUT')
                <div class="card-body">
                    <div class="row">
                        @include('artefactos.fields')
                    </div>
                </div>

                <div class="card-footer">
                    <button type="submit" class="btn btn-brand">Actualizar</button>
                    <a href="{{ route('artefactos.index') }}" class="btn btn-default">Cancelar</a>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('page_scripts')
<script>
    $(document).ready(function () {
        $('#tipo_artefacto_id').select2({
            placeholder: 'Seleccionar tipo...',
            allowClear: true
        });
    });
</script>
@endpush
