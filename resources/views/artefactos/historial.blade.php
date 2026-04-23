@extends('layouts.app')

@push('page_css')
<style>
    .errores-detalle { display: none; }
    .errores-detalle.show { display: table-row; }
</style>
@endpush

@section('content')
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1><i class="fas fa-history mr-2 text-brand"></i>Historial de Importaciones</h1>
                </div>
                <div class="col-sm-6 text-right">
                    <a href="{{ route('artefactos.index_importar') }}" class="btn btn-brand">
                        <i class="fas fa-file-import mr-1"></i> Nueva Importación
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

        <div class="card card-outline card-primary card-brand-top shadow-sm">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-table mr-2 text-brand"></i>Registro de subidas</h3>
            </div>
            <div class="card-body p-0">
                @if ($imports->isEmpty())
                    <div class="text-center py-5 text-muted">
                        <i class="fas fa-inbox fa-3x mb-3"></i>
                        <p>No hay importaciones registradas aún.</p>
                    </div>
                @else
                    <table class="table table-bordered table-hover mb-0">
                        <thead class="thead-light">
                            <tr>
                                <th>Fecha</th>
                                <th>Usuario</th>
                                <th>Archivo</th>
                                <th class="text-center">Total filas</th>
                                <th class="text-center">Creados</th>
                                <th class="text-center">Rechazados</th>
                                <th class="text-center">Errores</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($imports as $import)
                                <tr>
                                    <td>{{ $import->created_at->format('d/m/Y H:i') }}</td>
                                    <td>{{ $import->user->name ?? '—' }}</td>
                                    <td>
                                        <i class="fas fa-file-excel text-success mr-1"></i>
                                        {{ $import->archivo }}
                                    </td>
                                    <td class="text-center">{{ $import->total_rows }}</td>
                                    <td class="text-center">
                                        <span class="badge badge-success">{{ $import->success_count }}</span>
                                    </td>
                                    <td class="text-center">
                                        @if ($import->error_count > 0)
                                            <span class="badge badge-danger">{{ $import->error_count }}</span>
                                        @else
                                            <span class="badge badge-success">0</span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        @if ($import->error_count > 0)
                                            <button class="btn btn-xs btn-outline-danger btn-toggle-errores"
                                                    data-target="errores-{{ $import->id }}">
                                                <i class="fas fa-exclamation-triangle mr-1"></i>Ver errores
                                            </button>
                                        @else
                                            <i class="fas fa-check-circle text-success"></i>
                                        @endif
                                    </td>
                                </tr>
                                @if ($import->error_count > 0)
                                    <tr class="errores-detalle" id="errores-{{ $import->id }}">
                                        <td colspan="7" class="bg-light p-0">
                                            <div class="px-3 py-2">
                                                <strong class="text-danger">
                                                    <i class="fas fa-exclamation-circle mr-1"></i>
                                                    Detalle de filas rechazadas:
                                                </strong>
                                                <table class="table table-sm table-bordered mt-2 mb-0 bg-white">
                                                    <thead class="thead-light">
                                                        <tr>
                                                            <th style="width:80px">Fila</th>
                                                            <th>Motivo del rechazo</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        @foreach ($import->errors as $err)
                                                            <tr>
                                                                <td class="text-center">{{ $err['fila'] }}</td>
                                                                <td>{{ $err['motivo'] }}</td>
                                                            </tr>
                                                        @endforeach
                                                    </tbody>
                                                </table>
                                            </div>
                                        </td>
                                    </tr>
                                @endif
                            @endforeach
                        </tbody>
                    </table>
                @endif
            </div>
            @if ($imports->hasPages())
                <div class="card-footer">
                    {{ $imports->links() }}
                </div>
            @endif
        </div>
    </div>
@endsection

@push('page_scripts')
<script>
    $(document).on('click', '.btn-toggle-errores', function () {
        var target = '#' + $(this).data('target');
        $(target).toggleClass('show');
        var icon = $(this).find('i');
        if ($(target).hasClass('show')) {
            icon.removeClass('fa-exclamation-triangle').addClass('fa-times');
            $(this).text(' Ocultar errores').prepend(icon);
        } else {
            icon.removeClass('fa-times').addClass('fa-exclamation-triangle');
            $(this).text(' Ver errores').prepend(icon);
        }
    });
</script>
@endpush
