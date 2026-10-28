@extends('layouts.app')

@section('content')
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Configuracion Fiscal</h1>
                </div>
                <div class="col-sm-6 text-right">
                    <a href="{{ route('dte.configuracion.edit', $configuracion) }}" class="btn btn-brand">
                        <i class="fas fa-edit"></i> Editar
                    </a>
                </div>
            </div>
        </div>
    </section>

    <div class="content px-3">
        @include('flash::message')

        <div class="card card-brand-top">
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <table class="table table-sm">
                            <tr><th width="40%">RUT Empresa</th><td>{{ $configuracion->rut_empresa }}</td></tr>
                            <tr><th>Razon Social</th><td>{{ $configuracion->razon_social }}</td></tr>
                            <tr><th>Giro</th><td>{{ $configuracion->giro }}</td></tr>
                            <tr><th>Actividad Economica</th><td>{{ $configuracion->actividad_economica }}</td></tr>
                            <tr><th>Direccion</th><td>{{ $configuracion->direccion }}</td></tr>
                            <tr><th>Comuna</th><td>{{ $configuracion->comuna }}</td></tr>
                            <tr><th>Ciudad</th><td>{{ $configuracion->ciudad }}</td></tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <table class="table table-sm">
                            <tr><th width="40%">Telefono</th><td>{{ $configuracion->telefono ?? '-' }}</td></tr>
                            <tr><th>Email DTE</th><td>{{ $configuracion->email_dte ?? '-' }}</td></tr>
                            <tr><th>Sucursal SII</th><td>{{ $configuracion->sucursal_sii == 0 ? 'Casa Matriz' : $configuracion->sucursal_sii }}</td></tr>
                            <tr><th>Fecha Resolucion</th><td>{{ $configuracion->fecha_resolucion->format('d/m/Y') }}</td></tr>
                            <tr><th>Nro. Resolucion</th><td>{{ $configuracion->numero_resolucion }}</td></tr>
                            <tr><th>Ambiente</th><td>
                                <span class="badge badge-{{ $configuracion->isProduccion() ? 'success' : 'warning' }}">
                                    {{ ucfirst($configuracion->ambiente) }}
                                </span>
                            </td></tr>
                            <tr><th>Estado</th><td>
                                <span class="badge badge-{{ $configuracion->activa ? 'success' : 'secondary' }}">
                                    {{ $configuracion->activa ? 'Activa' : 'Inactiva' }}
                                </span>
                            </td></tr>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
