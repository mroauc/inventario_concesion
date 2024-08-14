@extends('layouts.app')

@push('page_css')
    <link rel="stylesheet" href="{!! asset('css/importacion.css') !!}">
@endpush

@section('content')
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Importador de Productos</h1>
                </div>
                {{-- <div class="col-sm-6">
                    <a class="btn btn-primary float-right"
                    href="{{ route('estadoNegocios.create') }}">
                        Nuevo Estado de Negocio
                    </a>
                </div> --}}
            </div>
        </div>
    </section>

    <div class="content px-3">

        @include('flash::message')

        <div class="clearfix"></div>

        <div class="card p-3">
            <form method="POST" action="{{ route('products.import') }}" enctype="multipart/form-data">
                @csrf
                <div class="card-body p-0">
                    {{-- <div class=""> --}}
                        <label for="">Bodega</label>
                        <select name="id_store" class="form-control mb-4 col-4">
                            <option value="">Selecccionar Bodega</option>
                            @foreach ($warehouses as $store)
                                <option value="{{$store->id}}">{{$store->name}}</option> 
                            @endforeach
                        </select>
                    {{-- </div> --}}
                    <input type="file" name="archivo" id="archivo" class="form-control-file">

                    <div class="card-footer clearfix">
                        <div class="float-right">
                            <button class="btn btn-default" type="submit">Importar</button>
                            <a href="{{ route('products.index') }}" class="btn btn-default">Volver</a>
                        </div>
                    </div>
                </div>
            </form>

        </div>

        {{-- @include('importador.seccionEstadoImportacion')
        @include('importador.seccionHistorialImportacion') --}}
        
    </div>
@endsection

@push('page_scripts')
    <script>

    </script>
@endpush