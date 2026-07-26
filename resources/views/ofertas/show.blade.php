@extends('layouts.app')

@section('content')
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Detalle de Oferta</h1>
                </div>
                <div class="col-sm-6">
                    <a class="btn btn-default float-right" href="{{ route('ofertas.index') }}">Volver</a>
                </div>
            </div>
        </div>
    </section>

    <div class="content px-3">
        <div class="card card-outline card-primary card-brand-top shadow-sm">
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <dl class="row">
                            <dt class="col-sm-4">Nombre</dt>
                            <dd class="col-sm-8">{{ $oferta->nombre }}</dd>

                            <dt class="col-sm-4">Precio</dt>
                            <dd class="col-sm-8">${{ number_format($oferta->precio, 0, ',', '.') }}</dd>

                            <dt class="col-sm-4">Estado de venta</dt>
                            <dd class="col-sm-8">
                                <span class="badge badge-{{ $oferta->vendido ? 'secondary' : 'success' }}">
                                    {{ $oferta->vendido ? 'Vendido' : 'Disponible' }}
                                </span>
                            </dd>

                            <dt class="col-sm-4">Publicación</dt>
                            <dd class="col-sm-8">
                                <span class="badge badge-{{ $oferta->estado ? 'info' : 'warning' }}">
                                    {{ $oferta->estado ? 'Visible en la landing' : 'Oculta' }}
                                </span>
                            </dd>

                            <dt class="col-sm-4">Descripción</dt>
                            <dd class="col-sm-8">{{ $oferta->descripcion ?: '—' }}</dd>

                            <dt class="col-sm-4">Creada</dt>
                            <dd class="col-sm-8">{{ $oferta->created_at->format('d/m/Y H:i') }}</dd>
                        </dl>
                    </div>

                    <div class="col-md-6">
                        <label class="d-block">Fotografías</label>
                        @if($oferta->fotos)
                            <div class="d-flex flex-wrap" style="gap:.5rem;">
                                @foreach($oferta->fotos as $foto)
                                    <a href="{{ asset('storage/' . $foto) }}" target="_blank">
                                        <img src="{{ asset('storage/' . $foto) }}" alt=""
                                             style="width:150px;height:150px;object-fit:cover;border-radius:6px;border:1px solid #dee2e6;">
                                    </a>
                                @endforeach
                            </div>
                        @else
                            <p class="text-muted">Sin fotografías.</p>
                        @endif
                    </div>
                </div>
            </div>

            <div class="card-footer">
                @can('ofertas.editar')
                    <a href="{{ route('ofertas.edit', $oferta->id) }}" class="btn btn-brand">Editar</a>
                @endcan
            </div>
        </div>
    </div>
@endsection
