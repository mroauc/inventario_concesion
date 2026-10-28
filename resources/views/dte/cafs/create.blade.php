@extends('layouts.app')

@section('content')
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-12">
                    <h1>Subir CAF (Folios Autorizados)</h1>
                </div>
            </div>
        </div>
    </section>

    <div class="content px-3">
        @include('adminlte-templates::common.errors')
        @include('flash::message')

        <div class="card card-brand-top">
            <form action="{{ route('dte.cafs.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 form-group required">
                            <label for="archivo_caf">Archivo CAF (XML)</label>
                            <input type="file" name="archivo_caf" id="archivo_caf" class="form-control-file" required
                                   accept=".xml">
                            <small class="text-muted">Archivo XML de autorizacion de folios descargado del SII</small>
                        </div>
                    </div>
                    <div class="alert alert-info mt-3">
                        <i class="fas fa-info-circle"></i>
                        El sistema validara automaticamente:
                        <ul class="mb-0 mt-1">
                            <li>Que el RUT coincida con la empresa configurada</li>
                            <li>Que el ambiente (IDK) coincida con la configuracion</li>
                            <li>Que el rango de folios no se solape con CAFs existentes</li>
                        </ul>
                    </div>
                </div>
                <div class="card-footer">
                    <button type="submit" class="btn btn-brand">Subir CAF</button>
                    <a href="{{ route('dte.cafs.index') }}" class="btn btn-default">Cancelar</a>
                </div>
            </form>
        </div>
    </div>
@endsection
