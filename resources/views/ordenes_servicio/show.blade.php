@extends('layouts.app')

@section('content')
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Orden de Servicio #{{ $orden->numero }}</h1>
                </div>
                <div class="col-sm-6">
                    <a class="btn btn-primary float-right" href="{{ route('ordenes_servicio.edit', $orden->id) }}">
                        Editar Orden
                    </a>
                </div>
            </div>
        </div>
    </section>

    <div class="content px-3">
        <div class="row">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Información General</h3>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <strong>Número:</strong> {{ $orden->numero }}<br>
                                <strong>Tipo de Servicio:</strong> {{ ucfirst($orden->tipo_servicio) }}<br>
                                <strong>Tipo de Atención:</strong> {{ ucfirst($orden->tipo_atencion) }}<br>
                                <strong>Estado:</strong> 
                                @switch($orden->estado)
                                    @case('pendiente')
                                        <span class="badge badge-warning">Pendiente</span>
                                        @break
                                    @case('en_progreso')
                                        <span class="badge badge-primary">En Progreso</span>
                                        @break
                                    @case('finalizada')
                                        <span class="badge badge-success">Finalizada</span>
                                        @break
                                    @case('cancelada')
                                        <span class="badge badge-danger">Cancelada</span>
                                        @break
                                @endswitch
                            </div>
                            <div class="col-md-6">
                                <strong>Fecha de Orden:</strong> {{ $orden->fecha_orden->format('d/m/Y H:i') }}<br>
                                @if($orden->fecha_visita)
                                    <strong>Fecha de Visita:</strong> {{ $orden->fecha_visita->format('d/m/Y H:i') }}<br>
                                @endif
                                @if($orden->folio_garantia)
                                    <strong>Folio Garantía:</strong> {{ $orden->folio_garantia }}<br>
                                @endif
                                @if($orden->valor_visita)
                                    <strong>Valor Visita:</strong> ${{ number_format($orden->valor_visita, 0, ',', '.') }}<br>
                                @endif
                            </div>
                        </div>

                        <hr>

                        <div class="row">
                            <div class="col-md-6">
                                <h5>Cliente</h5>
                                @if($orden->cliente->rut)
                                    <small class="text-muted">{{ $orden->cliente->rut }}</small><br>
                                @endif
                                <strong>{{ $orden->cliente->nombre }} {{ $orden->cliente->apellido }}</strong><br>
                                {{ $orden->cliente->direccion }}<br>
                                {{ $orden->cliente->numero_contacto }}<br>
                                @if($orden->cliente->email)
                                    {{ $orden->cliente->email }}
                                @endif
                            </div>
                            <div class="col-md-6">
                                @if($orden->artefacto)
                                    <h5>Artefacto</h5>
                                    <strong>{{ $orden->artefacto->nombre }}</strong><br>
                                    @if($orden->artefacto->marca)
                                        Marca: {{ $orden->artefacto->marca }}<br>
                                    @endif
                                    @if($orden->artefacto->modelo)
                                        Modelo: {{ $orden->artefacto->modelo }}<br>
                                    @endif
                                    @if($orden->artefacto->numero_serie)
                                        Serie: {{ $orden->artefacto->numero_serie }}
                                    @endif
                                @endif
                            </div>
                        </div>

                        @if($orden->tecnico)
                            <hr>
                            <h5>Técnico Asignado</h5>
                            <strong>{{ $orden->tecnico->nombre }}</strong><br>
                            @if($orden->tecnico->especialidad)
                                Especialidad: {{ $orden->tecnico->especialidad }}<br>
                            @endif
                            @if($orden->tecnico->telefono_contacto)
                                Teléfono: {{ $orden->tecnico->telefono_contacto }}
                            @endif
                        @endif

                        <hr>

                        <h5>Descripción de la Falla</h5>
                        <p>{{ $orden->descripcion_falla }}</p>

                        @if($orden->observaciones)
                            <h5>Observaciones</h5>
                            <p>{{ $orden->observaciones }}</p>
                        @endif
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Detalles del Servicio</h3>
                    </div>
                    <div class="card-body">
                        @if($orden->detalles->count() > 0)
                            @foreach($orden->detalles as $detalle)
                                <div class="border-bottom pb-2 mb-2">
                                    @if($detalle->producto)
                                        <strong>{{ $detalle->producto->name }}</strong>
                                        <small class="text-muted">(Producto)</small>
                                    @elseif($detalle->servicio)
                                        <strong>{{ $detalle->servicio->nombre_servicio }}</strong>
                                        <small class="text-muted">(Servicio)</small>
                                    @endif
                                    <br>
                                    Cantidad: {{ $detalle->cantidad }}<br>
                                    Precio Unit.: ${{ number_format($detalle->precio_unitario, 0, ',', '.') }}<br>
                                    <strong>Subtotal: ${{ number_format($detalle->subtotal, 0, ',', '.') }}</strong>
                                    @if($detalle->nota)
                                        <br><small class="text-muted">{{ $detalle->nota }}</small>
                                    @endif
                                </div>
                            @endforeach

                            <hr>
                            @if($orden->valor_visita)
                                <div class="d-flex justify-content-between">
                                    <span>Valor Visita:</span>
                                    <span>${{ number_format($orden->valor_visita, 0, ',', '.') }}</span>
                                </div>
                            @endif
                            <div class="d-flex justify-content-between">
                                <strong>Total:</strong>
                                <strong>${{ number_format($orden->costo_total, 0, ',', '.') }}</strong>
                            </div>
                        @else
                            <p class="text-muted">No hay detalles registrados.</p>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-12">
                <a href="{{ route('ordenes_servicio.index') }}" class="btn btn-secondary">Volver al Listado</a>
            </div>
        </div>
    </div>
@endsection