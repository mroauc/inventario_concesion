@extends('layouts.app')

@section('content')
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Detalle CAF</h1>
                </div>
                <div class="col-sm-6 text-right">
                    <a href="{{ route('dte.cafs.index') }}" class="btn btn-default">
                        <i class="fas fa-arrow-left"></i> Volver
                    </a>
                </div>
            </div>
        </div>
    </section>

    <div class="content px-3">
        <div class="card card-brand-top">
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <table class="table table-sm">
                            <tr><th width="40%">Tipo Documento</th><td>{{ \App\Models\Dte\CafFolio::nombreTipoDocumento($caf->tipo_documento) }} ({{ $caf->tipo_documento }})</td></tr>
                            <tr><th>Rango</th><td>{{ $caf->folio_desde }} - {{ $caf->folio_hasta }}</td></tr>
                            <tr><th>Folio Actual</th><td>{{ $caf->folio_actual }}</td></tr>
                            <tr><th>Folios Usados</th><td>{{ $caf->folios_usados }} de {{ $caf->folios_total }}</td></tr>
                            <tr><th>Folios Restantes</th><td><strong>{{ $caf->folios_restantes }}</strong></td></tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <table class="table table-sm">
                            <tr><th width="40%">Fecha Autorizacion</th><td>{{ $caf->fecha_autorizacion->format('d/m/Y') }}</td></tr>
                            <tr><th>Fecha Vencimiento</th><td>{{ $caf->fecha_vencimiento ? $caf->fecha_vencimiento->format('d/m/Y') : 'Sin vencimiento' }}</td></tr>
                            <tr><th>IDK</th><td>{{ $caf->idk }} ({{ $caf->idk == 300 ? 'Produccion' : ($caf->idk == 100 ? 'Certificacion' : 'Otro') }})</td></tr>
                            <tr><th>Agotado</th><td>{!! $caf->agotado ? '<span class="badge badge-dark">Si</span>' : '<span class="badge badge-success">No</span>' !!}</td></tr>
                            <tr><th>Vigente</th><td>{!! $caf->isVigente() ? '<span class="badge badge-success">Si</span>' : '<span class="badge badge-danger">No</span>' !!}</td></tr>
                        </table>
                    </div>
                </div>

                <div class="row mt-3">
                    <div class="col-12">
                        <h5>Uso de Folios</h5>
                        <div class="progress" style="height: 30px;">
                            @php
                                $pct = $caf->porcentaje_uso;
                                $color = $pct > 90 ? 'danger' : ($pct > 70 ? 'warning' : 'success');
                            @endphp
                            <div class="progress-bar bg-{{ $color }}" style="width: {{ $pct }}%">
                                {{ $caf->folios_usados }} / {{ $caf->folios_total }} ({{ $pct }}%)
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
