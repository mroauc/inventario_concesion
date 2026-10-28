@extends('layouts.app')

@section('content')
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-12">
                    <h1>Subir Certificado Digital</h1>
                </div>
            </div>
        </div>
    </section>

    <div class="content px-3">
        @include('adminlte-templates::common.errors')
        @include('flash::message')

        <div class="card card-brand-top">
            <form action="{{ route('dte.certificados.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 form-group required">
                            <label for="archivo">Archivo Certificado (.p12 / .pfx)</label>
                            <input type="file" name="archivo" id="archivo" class="form-control-file" required
                                   accept=".p12,.pfx">
                            <small class="text-muted">Certificado digital emitido por el SII o entidad certificadora</small>
                        </div>
                        <div class="col-md-6 form-group required">
                            <label for="password">Contrasena del Certificado</label>
                            <input type="password" name="password" id="password" class="form-control" required>
                            <small class="text-muted">Se almacenara cifrada (AES-256-CBC)</small>
                        </div>
                    </div>
                </div>
                <div class="card-footer">
                    <button type="submit" class="btn btn-brand">Subir Certificado</button>
                    <a href="{{ route('dte.certificados.index') }}" class="btn btn-default">Cancelar</a>
                </div>
            </form>
        </div>
    </div>
@endsection
