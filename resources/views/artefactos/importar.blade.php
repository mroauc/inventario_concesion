@extends('layouts.app')

@section('content')
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1><i class="fas fa-file-import mr-2 text-brand"></i>Importar Artefactos</h1>
                </div>
                <div class="col-sm-6 text-right">
                    <a href="{{ route('artefactos.historial') }}" class="btn btn-outline-brand">
                        <i class="fas fa-history mr-1"></i> Ver Historial
                    </a>
                    <a href="{{ route('artefactos.index') }}" class="btn btn-secondary ml-1">
                        <i class="fas fa-arrow-left mr-1"></i> Volver
                    </a>
                </div>
            </div>
        </div>
    </section>

    <div class="content px-3">
        @include('flash-message')

        <div class="row">
            <div class="col-md-7">
                <div class="card card-outline card-primary card-brand-top shadow-sm">
                    <div class="card-header">
                        <h3 class="card-title"><i class="fas fa-upload mr-2 text-brand"></i>Subir Archivo</h3>
                    </div>
                    <form method="POST" action="{{ route('artefactos.importar') }}" enctype="multipart/form-data">
                        @csrf
                        <div class="card-body">
                            @if ($errors->any())
                                <div class="alert alert-danger">
                                    <ul class="mb-0">
                                        @foreach ($errors->all() as $error)
                                            <li>{{ $error }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif

                            <div class="form-group">
                                <label for="archivo">Archivo Excel o CSV</label>
                                <input type="file" name="archivo" id="archivo"
                                       class="form-control-file @error('archivo') is-invalid @enderror"
                                       accept=".xlsx,.xls,.csv,.txt">
                                <small class="form-text text-muted">Formatos aceptados: .xlsx, .xls, .csv, .txt</small>
                                @error('archivo')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="card-footer text-right">
                            <button type="submit" class="btn btn-brand">
                                <i class="fas fa-file-import mr-1"></i> Importar
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <div class="col-md-5">
                <div class="card card-outline card-info shadow-sm">
                    <div class="card-header">
                        <h3 class="card-title"><i class="fas fa-info-circle mr-2"></i>Instrucciones</h3>
                    </div>
                    <div class="card-body p-0">
                        <table class="table table-sm table-bordered mb-0">
                            <thead class="thead-light">
                                <tr>
                                    <th class="text-center" style="width:50px">Col.</th>
                                    <th>Campo</th>
                                    <th>Observación</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td class="text-center font-weight-bold">1</td>
                                    <td>Código</td>
                                    <td>Obligatorio</td>
                                </tr>
                                <tr>
                                    <td class="text-center font-weight-bold">2</td>
                                    <td>Descripción</td>
                                    <td>Obligatorio</td>
                                </tr>
                                <tr>
                                    <td class="text-center font-weight-bold">3</td>
                                    <td>Modelo</td>
                                    <td>Obligatorio</td>
                                </tr>
                                <tr>
                                    <td class="text-center font-weight-bold">4</td>
                                    <td>Tipo de Artefacto</td>
                                    <td>Debe coincidir exactamente con un tipo existente</td>
                                </tr>
                                <tr>
                                    <td class="text-center font-weight-bold">5</td>
                                    <td>Marca</td>
                                    <td>Obligatorio</td>
                                </tr>
                            </tbody>
                        </table>
                        <div class="px-3 py-2">
                            <small class="text-muted">
                                <i class="fas fa-exclamation-triangle text-warning mr-1"></i>
                                La primera fila debe contener los encabezados y será ignorada en la importación.
                                Todas las celdas son obligatorias.
                            </small>
                        </div>
                        @if ($tipos->isNotEmpty())
                            <div class="px-3 pb-2">
                                <small class="font-weight-bold">Tipos disponibles:</small><br>
                                <small class="text-muted">{{ $tipos->pluck('nombre')->join(', ') }}</small>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
