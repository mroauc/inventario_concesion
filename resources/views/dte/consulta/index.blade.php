@extends('layouts.app')

@section('content')
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Consulta de Estado SII</h1>
                </div>
                <div class="col-sm-6 text-right">
                    <form action="{{ route('dte.consulta.sincronizar') }}" method="POST" class="d-inline">
                        @csrf
                        <button type="submit" class="btn btn-brand">
                            <i class="fas fa-sync-alt"></i> Sincronizar Todos
                        </button>
                    </form>
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
                            <th>Tipo</th>
                            <th>Folio</th>
                            <th>Fecha</th>
                            <th>Receptor</th>
                            <th class="text-right">Total</th>
                            <th>Track ID</th>
                            <th>Estado</th>
                            <th>Respuesta SII</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($documentos as $doc)
                        <tr>
                            <td>{{ $doc->nombre_tipo }}</td>
                            <td><strong>{{ $doc->folio }}</strong></td>
                            <td>{{ $doc->fecha_emision->format('d/m/Y') }}</td>
                            <td>{{ $doc->razon_social_receptor }}</td>
                            <td class="text-right">${{ number_format($doc->monto_total, 0, ',', '.') }}</td>
                            <td>
                                @if($doc->track_id)
                                    <code>{{ $doc->track_id }}</code>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td>
                                <span class="badge badge-{{ $doc->estado_badge }}">
                                    {{ ucfirst($doc->estado) }}
                                </span>
                            </td>
                            <td>
                                <small class="text-muted">{{ \Illuminate\Support\Str::limit($doc->respuesta_sii, 50) }}</small>
                            </td>
                            <td>
                                @if($doc->track_id && $doc->estado === 'enviado')
                                <form action="{{ route('dte.consulta.consultar', $doc) }}" method="POST" class="d-inline">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-outline-brand" title="Consultar estado">
                                        <i class="fas fa-search"></i>
                                    </button>
                                </form>
                                @endif
                                <a href="{{ route('dte.boletas.show', $doc) }}" class="btn btn-sm btn-outline-secondary" title="Ver detalle">
                                    <i class="fas fa-eye"></i>
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="9" class="text-center text-muted py-4">
                                No hay documentos con envios al SII.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($documentos->hasPages())
            <div class="card-footer">
                {{ $documentos->links() }}
            </div>
            @endif
        </div>
    </div>
@endsection
