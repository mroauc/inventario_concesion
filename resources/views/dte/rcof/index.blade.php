@extends('layouts.app')

@section('content')
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>RCOF — Reporte de Consumo de Folios</h1>
                </div>
            </div>
        </div>
    </section>

    <div class="content px-3">
        @include('flash::message')

        <div class="row">
            <div class="col-md-4">
                <div class="card card-brand-top">
                    <div class="card-header">
                        <h3 class="card-title">Enviar RCOF</h3>
                    </div>
                    <div class="card-body">
                        <p class="text-muted small">
                            El RCOF es un informe diario obligatorio que resume las boletas emitidas.
                            Debe enviarse al SII al final de cada dia de operacion.
                        </p>

                        <div class="row mb-3">
                            <div class="col-6 text-center">
                                <div class="text-muted small">Hoy ({{ \Carbon\Carbon::parse($hoy)->format('d/m') }})</div>
                                <div class="h3 mb-0">{{ $boletasHoy }}</div>
                                <div class="text-muted small">boletas</div>
                            </div>
                            <div class="col-6 text-center">
                                <div class="text-muted small">Ayer ({{ \Carbon\Carbon::parse($ayer)->format('d/m') }})</div>
                                <div class="h3 mb-0">{{ $boletasAyer }}</div>
                                <div class="text-muted small">boletas</div>
                            </div>
                        </div>

                        <form action="{{ route('dte.rcof.enviar') }}" method="POST" id="formRcof">
                            @csrf
                            <div class="form-group">
                                <label>Fecha del reporte</label>
                                <input type="date" name="fecha" class="form-control"
                                       value="{{ $ayer }}" max="{{ $hoy }}" required>
                                <small class="text-muted">Normalmente se envia el reporte del dia anterior.</small>
                            </div>
                            <button type="submit" class="btn btn-brand btn-block" id="btnEnviar">
                                <i class="fas fa-paper-plane mr-1"></i> Enviar RCOF al SII
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-md-8">
                <div class="card card-brand-top">
                    <div class="card-header">
                        <h3 class="card-title">Historial de Envios RCOF</h3>
                    </div>
                    <div class="card-body p-0">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Fecha Envio</th>
                                    <th>Track ID</th>
                                    <th>Status</th>
                                    <th>Secuencia</th>
                                    <th>Ambiente</th>
                                    <th>Resultado</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($envios as $envio)
                                <tr>
                                    <td>{{ $envio->created_at->format('d/m/Y H:i') }}</td>
                                    <td>
                                        @if($envio->track_id)
                                            <code>{{ $envio->track_id }}</code>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($envio->isExitoso())
                                            <span class="badge badge-success">OK</span>
                                        @else
                                            <span class="badge badge-danger">Error ({{ $envio->status_code }})</span>
                                        @endif
                                    </td>
                                    <td>{{ $envio->intentos }}</td>
                                    <td>
                                        <span class="badge badge-{{ $envio->ambiente === 'produccion' ? 'danger' : 'info' }}">
                                            {{ ucfirst($envio->ambiente) }}
                                        </span>
                                    </td>
                                    <td>
                                        @php
                                            $resp = json_decode($envio->respuesta_raw, true);
                                        @endphp
                                        <small class="text-muted">{{ $resp['glosa'] ?? '-' }}</small>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="6" class="text-center text-muted py-4">
                                        No se han enviado reportes RCOF aun.
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    @if($envios->hasPages())
                    <div class="card-footer">
                        {{ $envios->links() }}
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
document.getElementById('formRcof').addEventListener('submit', function() {
    var btn = document.getElementById('btnEnviar');
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin mr-1"></i> Enviando al SII...';
});
</script>
@endpush
