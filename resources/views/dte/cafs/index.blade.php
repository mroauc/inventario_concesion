@extends('layouts.app')

@section('content')
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Folios Autorizados (CAF)</h1>
                </div>
                <div class="col-sm-6 text-right">
                    <a href="{{ route('dte.cafs.create') }}" class="btn btn-brand">
                        <i class="fas fa-plus"></i> Subir CAF
                    </a>
                </div>
            </div>
        </div>
    </section>

    <div class="content px-3">
        @include('flash::message')

        <div class="card card-brand-top">
            <div class="card-body p-0">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Tipo Documento</th>
                            <th>Rango</th>
                            <th>Folios Usados</th>
                            <th>Restantes</th>
                            <th>Uso</th>
                            <th>Autorizacion</th>
                            <th>Vencimiento</th>
                            <th>Estado</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($cafs as $caf)
                        <tr>
                            <td>{{ \App\Models\Dte\CafFolio::nombreTipoDocumento($caf->tipo_documento) }} ({{ $caf->tipo_documento }})</td>
                            <td>{{ $caf->folio_desde }} - {{ $caf->folio_hasta }}</td>
                            <td>{{ $caf->folios_usados }}</td>
                            <td>
                                <strong class="{{ $caf->folios_restantes < 20 ? 'text-danger' : '' }}">
                                    {{ $caf->folios_restantes }}
                                </strong>
                            </td>
                            <td>
                                <div class="progress" style="height: 20px; min-width: 80px;">
                                    @php
                                        $pct = $caf->porcentaje_uso;
                                        $color = $pct > 90 ? 'danger' : ($pct > 70 ? 'warning' : 'success');
                                    @endphp
                                    <div class="progress-bar bg-{{ $color }}" style="width: {{ $pct }}%">
                                        {{ $pct }}%
                                    </div>
                                </div>
                            </td>
                            <td>{{ $caf->fecha_autorizacion->format('d/m/Y') }}</td>
                            <td>
                                @if($caf->fecha_vencimiento)
                                    {{ $caf->fecha_vencimiento->format('d/m/Y') }}
                                    @if(!$caf->isVigente())
                                        <span class="badge badge-danger">Vencido</span>
                                    @endif
                                @else
                                    <span class="text-muted">Sin vencimiento</span>
                                @endif
                            </td>
                            <td>
                                @if($caf->agotado)
                                    <span class="badge badge-dark">Agotado</span>
                                @elseif(!$caf->isVigente())
                                    <span class="badge badge-danger">Vencido</span>
                                @else
                                    <span class="badge badge-success">Disponible</span>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="text-center text-muted py-4">
                                No hay folios cargados. <a href="{{ route('dte.cafs.create') }}">Subir un CAF</a>.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
