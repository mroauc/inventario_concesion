@extends('layouts.app')

@section('content')
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Detalle Artefacto</h1>
                </div>
                <div class="col-sm-6">
                    <a href="{{ route('artefactos.index') }}" class="btn btn-default float-right">
                        <i class="fas fa-arrow-left mr-1"></i> Volver
                    </a>
                </div>
            </div>
        </div>
    </section>

    <div class="content px-3">
        <div class="card card-outline card-primary card-brand-top shadow-sm">
            <div class="card-body">
                <div class="row">
                    <div class="col-sm-6">
                        <strong>Tipo de Artefacto:</strong>
                        <p>{{ $artefacto->tipoArtefacto->nombre ?? '—' }}</p>
                    </div>
                    <div class="col-sm-6">
                        <strong>Estado:</strong>
                        <p>
                            <span class="badge badge-{{ $artefacto->estado ? 'success' : 'danger' }}">
                                {{ $artefacto->estado ? 'Activo' : 'Inactivo' }}
                            </span>
                        </p>
                    </div>
                    <div class="col-sm-6">
                        <strong>Marca:</strong>
                        <p>{{ $artefacto->marca ?? '—' }}</p>
                    </div>
                    <div class="col-sm-6">
                        <strong>Modelo:</strong>
                        <p>{{ $artefacto->modelo ?? '—' }}</p>
                    </div>
                    <div class="col-sm-12">
                        <strong>Descripción:</strong>
                        <p>{{ $artefacto->descripcion ?? '—' }}</p>
                    </div>
                </div>
            </div>

            <div class="card-footer">
                <a href="{{ route('artefactos.edit', $artefacto->id) }}" class="btn btn-brand">
                    <i class="fas fa-edit mr-1"></i> Editar
                </a>
                <a href="{{ route('artefactos.index') }}" class="btn btn-default">Cancelar</a>
            </div>
        </div>
    </div>
@endsection
