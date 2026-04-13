@extends('layouts.app')

@section('content')
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Órdenes de Servicio</h1>
                </div>
                <div class="col-sm-6">
                    <a class="btn btn-primary float-right" href="{{ route('ordenes_servicio.create') }}">
                        Crear Nueva Orden
                    </a>
                </div>
            </div>
        </div>
    </section>

    <div class="content px-3">
        @include('flash-message')

        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped" id="ordenes-table">
                        <thead>
                            <tr>
                                <th>Número</th>
                                <th>Cliente</th>
                                <th>Tipo Servicio</th>
                                <th>Fecha Orden</th>
                                <th>Estado</th>
                                <th>Técnico</th>
                                <th>Costo Total</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($ordenes as $orden)
                                <tr>
                                    <td>{{ $orden->numero }}</td>
                                    <td>{{ $orden->cliente->nombre }} {{ $orden->cliente->apellido }}</td>
                                    <td>
                                        <span class="badge badge-info">{{ ucfirst($orden->tipo_servicio) }}</span>
                                    </td>
                                    <td>{{ $orden->fecha_orden->format('d/m/Y H:i') }}</td>
                                    <td>
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
                                    </td>
                                    <td>{{ $orden->tecnico->nombre ?? 'Sin asignar' }}</td>
                                    <td>${{ number_format($orden->costo_total, 0, ',', '.') }}</td>
                                    <td>
                                        <div class="btn-group">
                                            <a href="{{ route('ordenes_servicio.show', $orden->id) }}" class="btn btn-info btn-sm">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="{{ route('ordenes_servicio.edit', $orden->id) }}" class="btn btn-primary btn-sm">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <form method="POST" action="{{ route('ordenes_servicio.destroy', $orden->id) }}" style="display: inline;">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('¿Está seguro?')">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

@push('page_scripts')
    <script>
        $(document).ready(function() {
            $('#ordenes-table').DataTable({
                "language": {
                    "url": "//cdn.datatables.net/plug-ins/1.10.25/i18n/Spanish.json"
                }
            });
        });
    </script>
@endpush
@endsection