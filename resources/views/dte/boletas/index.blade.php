@extends('layouts.app')

@section('content')
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Boletas Electrónicas</h1>
                </div>
                <div class="col-sm-6 text-right">
                    @can('dte.boletas.emitir')
                    <a href="{{ route('dte.boletas.create') }}" class="btn btn-brand">
                        <i class="fas fa-plus"></i> Emitir Boleta
                    </a>
                    @endcan
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
                            <th>Folio</th>
                            <th>Fecha</th>
                            <th>Receptor</th>
                            <th>RUT</th>
                            <th class="text-right">Total</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($boletas as $boleta)
                        <tr>
                            <td><strong>{{ $boleta->folio }}</strong></td>
                            <td>{{ $boleta->fecha_emision->format('d/m/Y') }}</td>
                            <td>{{ $boleta->razon_social_receptor }}</td>
                            <td>{{ $boleta->rut_receptor }}</td>
                            <td class="text-right">${{ number_format($boleta->monto_total, 0, ',', '.') }}</td>
                            <td>
                                <span class="badge badge-{{ $boleta->estado_badge }}">
                                    {{ ucfirst($boleta->estado) }}
                                </span>
                            </td>
                            <td>
                                <a href="{{ route('dte.boletas.show', $boleta) }}" class="btn btn-sm btn-outline-brand" title="Ver">
                                    <i class="fas fa-eye"></i>
                                </a>
                                @if($boleta->estado !== 'borrador')
                                <a href="{{ route('dte.boletas.pdf', $boleta) }}" class="btn btn-sm btn-outline-secondary" title="PDF">
                                    <i class="fas fa-file-pdf"></i>
                                </a>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted py-4">
                                No hay boletas emitidas.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($boletas->hasPages())
            <div class="card-footer">
                {{ $boletas->links() }}
            </div>
            @endif
        </div>
    </div>
@endsection
