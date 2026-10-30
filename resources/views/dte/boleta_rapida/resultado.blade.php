@extends('layouts.app')

@section('content')
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1><i class="fas fa-check-circle text-success"></i> Boleta Emitida</h1>
                </div>
            </div>
        </div>
    </section>

    <div class="content px-3">
        @include('flash::message')

        <div class="row">
            <div class="col-md-6">
                <div class="card card-brand-top">
                    <div class="card-header">
                        <h3 class="card-title">Boleta #{{ $boleta->folio }}</h3>
                    </div>
                    <div class="card-body">
                        <table class="table table-sm table-borderless">
                            <tr>
                                <td class="text-muted" style="width:40%">Tipo:</td>
                                <td>{{ $boleta->nombre_tipo }}</td>
                            </tr>
                            <tr>
                                <td class="text-muted">Folio:</td>
                                <td><strong>{{ $boleta->folio }}</strong></td>
                            </tr>
                            <tr>
                                <td class="text-muted">Fecha:</td>
                                <td>{{ $boleta->fecha_emision->format('d/m/Y') }}</td>
                            </tr>
                            <tr>
                                <td class="text-muted">Receptor:</td>
                                <td>{{ $boleta->razon_social_receptor }} ({{ $boleta->rut_receptor }})</td>
                            </tr>
                            <tr>
                                <td class="text-muted">Total:</td>
                                <td><strong style="font-size:1.3em">${{ number_format($boleta->monto_total, 0, ',', '.') }}</strong></td>
                            </tr>
                            <tr>
                                <td class="text-muted">Estado:</td>
                                <td>
                                    <span class="badge badge-{{ $boleta->estado_badge }} px-3 py-1" style="font-size:0.9em">
                                        {{ ucfirst($boleta->estado) }}
                                    </span>
                                </td>
                            </tr>
                            @if($boleta->track_id)
                            <tr>
                                <td class="text-muted">Track ID SII:</td>
                                <td><code>{{ $boleta->track_id }}</code></td>
                            </tr>
                            @endif
                        </table>
                    </div>
                </div>

                @if($boleta->detalles && $boleta->detalles->count())
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Detalle</h3>
                    </div>
                    <div class="card-body p-0">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Item</th>
                                    <th class="text-right">Cant.</th>
                                    <th class="text-right">P. Unit.</th>
                                    <th class="text-right">Subtotal</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($boleta->detalles as $detalle)
                                <tr>
                                    <td>{{ $detalle->nombre_item }}</td>
                                    <td class="text-right">{{ $detalle->cantidad }}</td>
                                    <td class="text-right">${{ number_format($detalle->precio_unitario, 0, ',', '.') }}</td>
                                    <td class="text-right">${{ number_format($detalle->monto_item, 0, ',', '.') }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
                @endif
            </div>

            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Vista previa PDF</h3>
                    </div>
                    <div class="card-body text-center p-2">
                        <iframe src="{{ route('dte.boleta_rapida.pdf', $boleta) }}"
                                style="width:100%; height:500px; border:1px solid #dee2e6; border-radius:4px;">
                        </iframe>
                    </div>
                </div>

                <div class="row">
                    <div class="col-6">
                        <a href="{{ route('dte.boleta_rapida.pdf', $boleta) }}" target="_blank"
                           class="btn btn-brand btn-block btn-lg" onclick="this.blur();">
                            <i class="fas fa-print mr-1"></i> Imprimir
                        </a>
                    </div>
                    <div class="col-6">
                        <a href="{{ route('dte.boleta_rapida.create') }}" class="btn btn-success btn-block btn-lg">
                            <i class="fas fa-plus mr-1"></i> Nueva Venta
                        </a>
                    </div>
                </div>

                <div class="mt-3 text-center">
                    <a href="{{ route('dte.boletas.show', $boleta) }}" class="text-muted">
                        <i class="fas fa-eye"></i> Ver detalle completo
                    </a>
                    &nbsp;&middot;&nbsp;
                    <a href="{{ route('dte.boletas.index') }}" class="text-muted">
                        <i class="fas fa-list"></i> Todas las boletas
                    </a>
                </div>
            </div>
        </div>
    </div>
@endsection
