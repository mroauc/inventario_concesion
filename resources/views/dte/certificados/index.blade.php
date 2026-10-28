@extends('layouts.app')

@section('content')
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Certificados Digitales</h1>
                </div>
                <div class="col-sm-6 text-right">
                    <a href="{{ route('dte.certificados.create') }}" class="btn btn-brand">
                        <i class="fas fa-plus"></i> Subir Certificado
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
                            <th>Titular</th>
                            <th>RUT</th>
                            <th>Vencimiento</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($certificados as $cert)
                        <tr>
                            <td>{{ $cert->nombre_titular }}</td>
                            <td>{{ $cert->rut_titular }}</td>
                            <td>
                                {{ $cert->fecha_vencimiento->format('d/m/Y') }}
                                @if(!$cert->isVigente())
                                    <span class="badge badge-danger">Vencido</span>
                                @elseif($cert->fecha_vencimiento->diffInDays(now()) < 30)
                                    <span class="badge badge-warning">Por vencer</span>
                                @endif
                            </td>
                            <td>
                                @if($cert->activo)
                                    <span class="badge badge-success">Activo</span>
                                @else
                                    <span class="badge badge-secondary">Inactivo</span>
                                @endif
                            </td>
                            <td>
                                @if(!$cert->activo)
                                    <form action="{{ route('dte.certificados.activar', $cert) }}" method="POST" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-outline-brand" title="Activar">
                                            <i class="fas fa-check"></i>
                                        </button>
                                    </form>
                                    <form action="{{ route('dte.certificados.destroy', $cert) }}" method="POST" class="d-inline"
                                          onsubmit="return confirm('Eliminar este certificado?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger" title="Eliminar">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="text-center text-muted py-4">
                                No hay certificados cargados. <a href="{{ route('dte.certificados.create') }}">Subir uno</a>.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
