@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card card-outline card-primary card-brand-top shadow-sm">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-envelope mr-2 text-brand"></i>Mensajes de Contacto</h3>
                </div>
                <div class="card-body p-0">

                    @if(session('success'))
                        <div class="alert alert-success m-3">{{ session('success') }}</div>
                    @endif

                    @if($mensajes->isEmpty())
                        <p class="text-muted p-4 mb-0">No hay mensajes recibidos.</p>
                    @else
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="thead-light">
                                <tr>
                                    <th>Fecha</th>
                                    <th>Nombre</th>
                                    <th>Email</th>
                                    <th>Teléfono</th>
                                    <th>Asunto</th>
                                    <th>Mensaje</th>
                                    <th>Estado</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($mensajes as $msg)
                                <tr class="{{ $msg->leido ? '' : 'font-weight-bold' }}">
                                    <td class="text-nowrap">{{ $msg->created_at->format('d/m/Y H:i') }}</td>
                                    <td>{{ $msg->nombre }}</td>
                                    <td><a href="mailto:{{ $msg->email }}">{{ $msg->email }}</a></td>
                                    <td>{{ $msg->telefono ?? '—' }}</td>
                                    <td>{{ $msg->asunto ?? '—' }}</td>
                                    <td style="max-width:300px;white-space:pre-wrap;">{{ $msg->mensaje }}</td>
                                    <td>
                                        @can('mensajes.editar')
                                        <form action="{{ route('mensajes.leido', $msg) }}" method="POST">
                                            @csrf @method('PATCH')
                                            <button class="btn btn-xs {{ $msg->leido ? 'btn-secondary' : 'btn-success' }}">
                                                {{ $msg->leido ? 'No leído' : 'Leído' }}
                                            </button>
                                        </form>
                                        @endcan
                                    </td>
                                    <td>
                                        @can('mensajes.eliminar')
                                        <form action="{{ route('mensajes.destroy', $msg) }}" method="POST"
                                              onsubmit="return confirm('¿Eliminar este mensaje?')">
                                            @csrf @method('DELETE')
                                            <button class="btn btn-xs btn-danger"><i class="fas fa-trash"></i></button>
                                        </form>
                                        @endcan
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="p-3">{{ $mensajes->links() }}</div>
                    @endif

                </div>
            </div>
        </div>
    </div>
</div>
@endsection
