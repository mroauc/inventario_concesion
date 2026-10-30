@extends('layouts.app')

@section('content')
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Boleta Electrónica #{{ $boleta->folio }}</h1>
                </div>
                <div class="col-sm-6 text-right">
                    <a href="{{ route('dte.boletas.pdf', $boleta) }}" class="btn btn-secondary">
                        <i class="fas fa-file-pdf mr-1"></i> Descargar PDF
                    </a>
                </div>
            </div>
        </div>
    </section>

    <div class="content px-3">
        @include('flash::message')

        <div class="row">
            <div class="col-md-8">
                <div class="card card-brand-top">
                    <div class="card-header">
                        <h3 class="card-title">Información del Documento</h3>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <strong>Tipo:</strong> Boleta Electrónica (39)<br>
                                <strong>Folio:</strong> {{ $boleta->folio }}<br>
                                <strong>Fecha Emisión:</strong> {{ $boleta->fecha_emision->format('d/m/Y') }}<br>
                                <strong>Estado:</strong>
                                <span class="badge badge-{{ $boleta->estado_badge }}">
                                    {{ ucfirst($boleta->estado) }}
                                </span>
                            </div>
                            <div class="col-md-6">
                                <strong>RUT Emisor:</strong> {{ $boleta->rut_emisor }}<br>
                                <strong>RUT Receptor:</strong> {{ $boleta->rut_receptor }}<br>
                                <strong>Receptor:</strong> {{ $boleta->razon_social_receptor }}<br>
                                @if($boleta->track_id)
                                    <strong>Track ID SII:</strong> {{ $boleta->track_id }}<br>
                                @endif
                            </div>
                        </div>

                        @if($boleta->ordenServicio)
                        <hr>
                        <strong>Orden de Servicio:</strong>
                        <a href="{{ route('ordenes_servicio.show', $boleta->ordenServicio->id) }}">
                            #{{ $boleta->ordenServicio->numero }}
                        </a>
                        @endif

                        @if($boleta->respuesta_sii)
                        <hr>
                        <strong>Respuesta SII:</strong>
                        <p class="text-muted mb-0">{{ $boleta->respuesta_sii }}</p>
                        @endif
                    </div>
                </div>

                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Detalle</h3>
                    </div>
                    <div class="card-body p-0">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Item</th>
                                    <th>Cantidad</th>
                                    <th class="text-right">Precio Unit.</th>
                                    <th class="text-right">Descuento</th>
                                    <th class="text-right">Subtotal</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($boleta->detalles as $detalle)
                                <tr>
                                    <td>{{ $detalle->nro_linea }}</td>
                                    <td>
                                        {{ $detalle->nombre_item }}
                                        @if($detalle->descripcion)
                                            <br><small class="text-muted">{{ $detalle->descripcion }}</small>
                                        @endif
                                    </td>
                                    <td>{{ $detalle->cantidad }}</td>
                                    <td class="text-right">${{ number_format($detalle->precio_unitario, 0, ',', '.') }}</td>
                                    <td class="text-right">
                                        @if($detalle->descuento_monto > 0)
                                            -${{ number_format($detalle->descuento_monto, 0, ',', '.') }}
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td class="text-right">${{ number_format($detalle->monto_item, 0, ',', '.') }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                            <tfoot>
                                <tr>
                                    <td colspan="5" class="text-right">Neto:</td>
                                    <td class="text-right">${{ number_format($boleta->monto_neto, 0, ',', '.') }}</td>
                                </tr>
                                <tr>
                                    <td colspan="5" class="text-right">IVA ({{ (int)$boleta->tasa_iva }}%):</td>
                                    <td class="text-right">${{ number_format($boleta->monto_iva, 0, ',', '.') }}</td>
                                </tr>
                                @if($boleta->monto_exento > 0)
                                <tr>
                                    <td colspan="5" class="text-right">Exento:</td>
                                    <td class="text-right">${{ number_format($boleta->monto_exento, 0, ',', '.') }}</td>
                                </tr>
                                @endif
                                <tr>
                                    <td colspan="5" class="text-right"><strong>Total:</strong></td>
                                    <td class="text-right"><strong>${{ number_format($boleta->monto_total, 0, ',', '.') }}</strong></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Historial de Envíos SII</h3>
                    </div>
                    <div class="card-body">
                        @forelse($boleta->envios as $envio)
                        <div class="border-bottom pb-2 mb-2">
                            <small class="text-muted">{{ $envio->created_at->format('d/m/Y H:i') }}</small><br>
                            <strong>Track ID:</strong> {{ $envio->track_id ?? '-' }}<br>
                            <strong>Status:</strong>
                            @if($envio->isExitoso())
                                <span class="text-success">OK ({{ $envio->status_code }})</span>
                            @else
                                <span class="text-danger">Error ({{ $envio->status_code }})</span>
                            @endif
                            <br>
                            <strong>Intento:</strong> {{ $envio->intentos }}
                        </div>
                        @empty
                        <p class="text-muted mb-0">Sin envíos registrados.</p>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-12">
                <a href="{{ route('dte.boletas.index') }}" class="btn btn-secondary">Volver al Listado</a>
            </div>
        </div>
    </div>
@endsection
