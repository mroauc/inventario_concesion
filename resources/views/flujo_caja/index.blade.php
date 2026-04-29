@extends('layouts.app')

@section('content')
<section class="content-header">
    <div class="container-fluid">
        <div class="row mb-2 align-items-center">
            <div class="col-sm-6">
                <h1><i class="fas fa-cash-register text-brand mr-2"></i>Flujo de Caja</h1>
            </div>
            <div class="col-sm-6 d-flex justify-content-end align-items-center gap-2">
                <a href="{{ route('flujo_caja.logs.index') }}" class="btn btn-outline-brand btn-sm mr-2">
                    <i class="fas fa-history mr-1"></i> Ver historial
                </a>
                <div class="input-group" style="max-width:200px;">
                    <input type="date" id="selector-fecha" class="form-control"
                           value="{{ $fecha->toDateString() }}"
                           max="{{ \Carbon\Carbon::today()->toDateString() }}">
                    <div class="input-group-append">
                        <span class="input-group-text"><i class="fas fa-calendar-alt"></i></span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<div class="content px-3" id="flujo-caja-app">
    @include('flash-message')

    {{-- BADGES de estado --}}
    <div class="mb-3 d-flex align-items-center gap-2">
        <span id="badge-estado"
              class="badge badge-lg {{ $caja->isAbierta() ? 'badge-success' : 'badge-secondary' }} px-3 py-2"
              style="font-size:0.95rem;">
            <i class="fas {{ $caja->isAbierta() ? 'fa-lock-open' : 'fa-lock' }} mr-1"></i>
            {{ $caja->isAbierta() ? 'Caja abierta' : 'Caja cerrada' }}
        </span>
        <small class="text-muted ml-2" id="fecha-display">{{ $fecha->locale('es')->isoFormat('dddd D [de] MMMM [de] YYYY') }}</small>
    </div>

    {{-- FILA SUPERIOR: Apertura caja chica + Apertura Tecnoelectro --}}
    <div class="row">
        {{-- Caja Chica --}}
        <div class="col-md-6">
            <div class="card card-outline card-success card-brand-top shadow-sm">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-coins text-brand mr-1"></i> Caja Chica</h3>
                </div>
                <div class="card-body">
                    <div class="form-group mb-0">
                        <label class="font-weight-bold">Apertura caja chica</label>
                        <div class="input-group" style="max-width:220px;">
                            <div class="input-group-prepend"><span class="input-group-text">$</span></div>
                            <input type="number" id="apertura-caja" class="form-control"
                                   value="{{ number_format($caja->apertura_caja, 0, '.', '') }}"
                                   step="1" min="0"
                                   {{ !$caja->isAbierta() ? 'disabled' : '' }}>
                            <div class="input-group-append">
                                <button class="btn btn-outline-brand btn-guardar-apertura"
                                        data-campo="apertura_caja"
                                        {{ !$caja->isAbierta() ? 'disabled' : '' }}>
                                    <i class="fas fa-save"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Tecnoelectro --}}
        <div class="col-md-6">
            <div class="card card-outline card-primary card-brand-top shadow-sm">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-file-invoice-dollar text-brand mr-1"></i> Tecnoelectro</h3>
                </div>
                <div class="card-body">
                    <div class="form-group mb-0">
                        <label class="font-weight-bold">Apertura Tecnoelectro</label>
                        <div class="input-group" style="max-width:220px;">
                            <div class="input-group-prepend"><span class="input-group-text">$</span></div>
                            <input type="number" id="apertura-tecnoelectro" class="form-control"
                                   value="{{ number_format($caja->apertura_tecnoelectro, 0, '.', '') }}"
                                   step="1" min="0"
                                   {{ !$caja->isAbierta() ? 'disabled' : '' }}>
                            <div class="input-group-append">
                                <button class="btn btn-outline-brand btn-guardar-apertura"
                                        data-campo="apertura_tecnoelectro"
                                        {{ !$caja->isAbierta() ? 'disabled' : '' }}>
                                    <i class="fas fa-save"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- REGISTRO DE MOVIMIENTO --}}
    @if($caja->isAbierta())
    <div class="card card-outline card-warning shadow-sm" id="card-nuevo-movimiento">
        <div class="card-header">
            <h3 class="card-title"><i class="fas fa-plus-circle text-warning mr-1"></i> Registrar Movimiento</h3>
            <div class="card-tools">
                <button type="button" class="btn btn-tool" data-card-widget="collapse">
                    <i class="fas fa-minus"></i>
                </button>
            </div>
        </div>
        <div class="card-body">
            <form id="form-movimiento">
                <input type="hidden" name="caja_id" value="{{ $caja->id }}">
                <div class="row">
                    <div class="col-md-3 form-group required">
                        <label>Tipo</label>
                        <select name="tipo_movimiento" class="form-control" required>
                            <option value="">— Seleccionar —</option>
                            <option value="ingreso">Ingreso</option>
                            <option value="egreso">Egreso</option>
                        </select>
                    </div>
                    <div class="col-md-3 form-group required">
                        <label>Medio</label>
                        <select name="medio" class="form-control" required>
                            <option value="">— Seleccionar —</option>
                            <optgroup label="Caja Chica">
                                <option value="efectivo">Efectivo</option>
                                <option value="credito_debito">Crédito/Débito</option>
                                <option value="transferencia">Transferencia</option>
                                <option value="deposito_banco">Depósito Banco</option>
                            </optgroup>
                            <optgroup label="Tecnoelectro">
                                <option value="efectivo_tecno">Efectivo Tecnoelectro</option>
                                <option value="credito_debito_tecno">Créd./Déb. Tecnoelectro</option>
                                <option value="devolucion_abono">Devolución Abono</option>
                                <option value="deposito_banco_tecnoelectro">Depósito Banco Tecnoelectro</option>
                            </optgroup>
                        </select>
                    </div>
                    <div class="col-md-2 form-group required">
                        <label>Monto</label>
                        <div class="input-group">
                            <div class="input-group-prepend"><span class="input-group-text">$</span></div>
                            <input type="number" name="monto" class="form-control" step="1" required placeholder="0">
                        </div>
                    </div>
                    <div class="col-md-4 form-group required">
                        <label>Detalle</label>
                        <input type="text" name="detalle" required class="form-control" maxlength="255" placeholder="Descripción opcional">
                    </div>
                </div>
                <div class="row">
                    <div class="col-12">
                        <button type="submit" class="btn btn-brand">
                            <i class="fas fa-save mr-1"></i> Registrar
                        </button>
                        <button type="reset" class="btn btn-outline-secondary ml-2">Limpiar</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
    @endif

    {{-- TABLA DE MOVIMIENTOS: Caja Chica --}}
    <div class="card card-outline card-secondary shadow-sm">
        <div class="card-header">
            <h3 class="card-title"><i class="fas fa-coins text-brand mr-1"></i> Movimientos Caja Chica</h3>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-sm table-hover mb-0" id="tabla-movimientos">
                    <thead class="thead-light">
                        <tr>
                            <th style="width:60px">Hora</th>
                            <th>Tipo</th>
                            <th>Medio</th>
                            <th>Detalle</th>
                            <th class="text-right">Monto</th>
                            <th>Usuario</th>
                            <th style="width:80px">Estado</th>
                            @if($caja->isAbierta())
                            <th style="width:60px"></th>
                            @endif
                        </tr>
                    </thead>
                    <tbody id="tbody-movimientos">
                        @php $movsCajaChica = $movimientos->filter(fn($m) => !$m->esTecnoelectro()); @endphp
                        @forelse($movsCajaChica as $mov)
                        <tr id="fila-{{ $mov->id }}" class="{{ $mov->anulado ? 'text-muted' : '' }}">
                            <td>{{ $mov->created_at->timezone(config('app.timezone'))->format('H:i') }}</td>
                            <td>
                                @if(!$mov->anulado)
                                    <span class="badge {{ $mov->tipo_movimiento === 'ingreso' ? 'badge-success' : 'badge-danger' }}">
                                        {{ $mov->getTipoLabel() }}
                                    </span>
                                @else
                                    <span class="badge badge-secondary">{{ $mov->getTipoLabel() }}</span>
                                @endif
                            </td>
                            <td>{{ $mov->getMedioLabel() }}</td>
                            <td>{{ $mov->detalle ?? '—' }}</td>
                            <td class="text-right {{ $mov->anulado ? '' : ($mov->tipo_movimiento === 'ingreso' ? 'text-success font-weight-bold' : 'text-danger font-weight-bold') }}">
                                {{ $mov->anulado ? '' : ($mov->tipo_movimiento === 'egreso' ? '-' : '+') }}${{ number_format($mov->monto, 0, ',', '.') }}
                            </td>
                            <td>{{ $mov->usuario->name ?? '—' }}</td>
                            <td>
                                @if($mov->anulado)
                                    <span class="badge badge-secondary">Anulado</span>
                                @else
                                    <span class="badge badge-success">Activo</span>
                                @endif
                            </td>
                            @if($caja->isAbierta())
                            <td>
                                @if(!$mov->anulado)
                                <button class="btn btn-xs btn-outline-danger btn-anular"
                                        data-id="{{ $mov->id }}"
                                        title="Anular movimiento">
                                    <i class="fas fa-ban"></i>
                                </button>
                                @endif
                            </td>
                            @endif
                        </tr>
                        @empty
                        <tr id="fila-vacia">
                            <td colspan="{{ $caja->isAbierta() ? 8 : 7 }}" class="text-center text-muted py-3">
                                Sin movimientos de caja chica para este día.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- TABLA DE MOVIMIENTOS: Tecnoelectro --}}
    <div class="card card-outline card-primary shadow-sm">
        <div class="card-header">
            <h3 class="card-title"><i class="fas fa-file-invoice-dollar text-brand mr-1"></i> Movimientos Tecnoelectro</h3>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-sm table-hover mb-0" id="tabla-movimientos-tecno">
                    <thead class="thead-light">
                        <tr>
                            <th style="width:60px">Hora</th>
                            <th>Tipo</th>
                            <th>Medio</th>
                            <th>Detalle</th>
                            <th class="text-right">Monto</th>
                            <th>Usuario</th>
                            <th style="width:80px">Estado</th>
                            @if($caja->isAbierta())
                            <th style="width:60px"></th>
                            @endif
                        </tr>
                    </thead>
                    <tbody id="tbody-movimientos-tecno">
                        @php $movsTecno = $movimientos->filter(fn($m) => $m->esTecnoelectro()); @endphp
                        @forelse($movsTecno as $mov)
                        <tr id="fila-{{ $mov->id }}" class="{{ $mov->anulado ? 'text-muted' : '' }}">
                            <td>{{ $mov->created_at->timezone(config('app.timezone'))->format('H:i') }}</td>
                            <td>
                                @if(!$mov->anulado)
                                    <span class="badge {{ $mov->tipo_movimiento === 'ingreso' ? 'badge-success' : 'badge-danger' }}">
                                        {{ $mov->getTipoLabel() }}
                                    </span>
                                @else
                                    <span class="badge badge-secondary">{{ $mov->getTipoLabel() }}</span>
                                @endif
                            </td>
                            <td>{{ $mov->getMedioLabel() }}</td>
                            <td>{{ $mov->detalle ?? '—' }}</td>
                            <td class="text-right {{ $mov->anulado ? '' : ($mov->tipo_movimiento === 'ingreso' ? 'text-success font-weight-bold' : 'text-danger font-weight-bold') }}">
                                {{ $mov->anulado ? '' : ($mov->tipo_movimiento === 'egreso' ? '-' : '+') }}${{ number_format($mov->monto, 0, ',', '.') }}
                            </td>
                            <td>{{ $mov->usuario->name ?? '—' }}</td>
                            <td>
                                @if($mov->anulado)
                                    <span class="badge badge-secondary">Anulado</span>
                                @else
                                    <span class="badge badge-success">Activo</span>
                                @endif
                            </td>
                            @if($caja->isAbierta())
                            <td>
                                @if(!$mov->anulado)
                                <button class="btn btn-xs btn-outline-danger btn-anular"
                                        data-id="{{ $mov->id }}"
                                        title="Anular movimiento">
                                    <i class="fas fa-ban"></i>
                                </button>
                                @endif
                            </td>
                            @endif
                        </tr>
                        @empty
                        <tr id="fila-vacia-tecno">
                            <td colspan="{{ $caja->isAbierta() ? 8 : 7 }}" class="text-center text-muted py-3">
                                Sin movimientos Tecnoelectro para este día.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- RESUMEN DEL DÍA --}}
    <div class="row" id="seccion-resumen">

        {{-- Columna izquierda: totales por medio --}}
        <div class="col-md-8">
            {{-- Resumen Caja Chica --}}
            <div class="card card-outline card-info card-brand-top shadow-sm">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-table text-brand mr-1"></i> Resumen Caja Chica por medio</h3>
                </div>
                <div class="card-body p-0">
                    <table class="table table-sm mb-0">
                        <thead class="thead-light">
                            <tr>
                                <th>Medio</th>
                                <th class="text-right text-success">Ingresos</th>
                                <th class="text-right text-danger">Egresos</th>
                                <th class="text-right">Neto</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>Efectivo</td>
                                <td class="text-right text-success" id="res-ing-efectivo">${{ number_format($totales['ingreso_efectivo'], 0, ',', '.') }}</td>
                                <td class="text-right text-danger" id="res-egr-efectivo">-${{ number_format($totales['egreso_efectivo'], 0, ',', '.') }}</td>
                                <td class="text-right font-weight-bold" id="res-net-efectivo">${{ number_format($totales['ingreso_efectivo'] - $totales['egreso_efectivo'], 0, ',', '.') }}</td>
                            </tr>
                            <tr>
                                <td>Crédito/Débito</td>
                                <td class="text-right text-success" id="res-ing-credito">${{ number_format($totales['ingreso_credito_debito'], 0, ',', '.') }}</td>
                                <td class="text-right text-danger" id="res-egr-credito">-${{ number_format($totales['egreso_credito_debito'], 0, ',', '.') }}</td>
                                <td class="text-right font-weight-bold" id="res-net-credito">${{ number_format($totales['ingreso_credito_debito'] - $totales['egreso_credito_debito'], 0, ',', '.') }}</td>
                            </tr>
                            <tr>
                                <td>Transferencia</td>
                                <td class="text-right text-success" id="res-ing-trans">${{ number_format($totales['ingreso_transferencia'], 0, ',', '.') }}</td>
                                <td class="text-right text-danger" id="res-egr-trans">-${{ number_format($totales['egreso_transferencia'], 0, ',', '.') }}</td>
                                <td class="text-right font-weight-bold" id="res-net-trans">${{ number_format($totales['ingreso_transferencia'] - $totales['egreso_transferencia'], 0, ',', '.') }}</td>
                            </tr>
                            <tr class="table-light font-weight-bold">
                                <td>Depósito Banco</td>
                                <td class="text-right text-danger" colspan="2" id="res-deposito-banco">-${{ number_format($totales['deposito_banco'], 0, ',', '.') }}</td>
                                <td></td>
                            </tr>
                        </tbody>
                        <tfoot class="thead-light">
                            <tr class="font-weight-bold">
                                <td>TOTALES</td>
                                <td class="text-right text-success" id="res-total-ing">+${{ number_format($totales['total_ingresos'], 0, ',', '.') }}</td>
                                <td class="text-right text-danger" id="res-total-egr">-${{ number_format($totales['total_egresos'], 0, ',', '.') }}</td>
                                <td class="text-right font-weight-bold" id="res-total-net">${{ number_format($totales['total_ingresos'] - $totales['total_egresos'], 0, ',', '.') }}</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>

            {{-- Resumen Tecnoelectro --}}
            <div class="card card-outline card-primary card-brand-top shadow-sm">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-file-invoice-dollar text-brand mr-1"></i> Resumen Tecnoelectro por medio</h3>
                </div>
                <div class="card-body p-0">
                    <table class="table table-sm mb-0">
                        <thead class="thead-light">
                            <tr>
                                <th>Medio</th>
                                <th class="text-right text-success">Ingresos</th>
                                <th class="text-right text-danger">Egresos</th>
                                <th class="text-right">Neto</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>Efectivo Tecnoelectro</td>
                                <td class="text-right text-success" id="res-ing-efectivo-tecno">${{ number_format($totales['ingreso_efectivo_tecno'], 0, ',', '.') }}</td>
                                <td class="text-right text-danger" id="res-egr-efectivo-tecno">-${{ number_format($totales['egreso_efectivo_tecno'], 0, ',', '.') }}</td>
                                <td class="text-right font-weight-bold" id="res-net-efectivo-tecno">${{ number_format($totales['ingreso_efectivo_tecno'] - $totales['egreso_efectivo_tecno'], 0, ',', '.') }}</td>
                            </tr>
                            <tr>
                                <td>Créd./Déb. Tecnoelectro</td>
                                <td class="text-right text-success" id="res-ing-credito-tecno">${{ number_format($totales['ingreso_credito_debito_tecno'], 0, ',', '.') }}</td>
                                <td class="text-right text-danger" id="res-egr-credito-tecno">-${{ number_format($totales['egreso_credito_debito_tecno'], 0, ',', '.') }}</td>
                                <td class="text-right font-weight-bold" id="res-net-credito-tecno">${{ number_format($totales['ingreso_credito_debito_tecno'] - $totales['egreso_credito_debito_tecno'], 0, ',', '.') }}</td>
                            </tr>
                            <tr>
                                <td>Devolución Abono</td>
                                <td class="text-right text-success" id="res-ing-devolucion-abono">${{ number_format($totales['ingreso_devolucion_abono'], 0, ',', '.') }}</td>
                                <td class="text-right text-danger" id="res-egr-devolucion-abono">-${{ number_format($totales['egreso_devolucion_abono'], 0, ',', '.') }}</td>
                                <td class="text-right font-weight-bold" id="res-net-devolucion-abono">${{ number_format($totales['ingreso_devolucion_abono'] - $totales['egreso_devolucion_abono'], 0, ',', '.') }}</td>
                            </tr>
                            <tr class="table-light font-weight-bold">
                                <td>Depósito Banco Tecnoelectro</td>
                                <td class="text-right text-danger" colspan="2" id="res-deposito-banco-tecno">-${{ number_format($totales['deposito_banco_tecnoelectro'], 0, ',', '.') }}</td>
                                <td></td>
                            </tr>
                        </tbody>
                        <tfoot class="thead-light">
                            <tr class="font-weight-bold">
                                <td>TOTALES</td>
                                <td class="text-right text-success" id="res-total-ing-tecno">+${{ number_format($totales['total_ingresos_tecno'], 0, ',', '.') }}</td>
                                <td class="text-right text-danger" id="res-total-egr-tecno">-${{ number_format($totales['total_egresos_tecno'], 0, ',', '.') }}</td>
                                <td class="text-right font-weight-bold" id="res-total-net-tecno">${{ number_format($totales['total_ingresos_tecno'] - $totales['total_egresos_tecno'], 0, ',', '.') }}</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>

        {{-- Columna derecha: cierres --}}
        <div class="col-md-4">
            {{-- Cierre caja chica --}}
            <div class="card card-outline card-success card-brand-top shadow-sm">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-coins text-brand mr-1"></i> Cierre Caja Chica</h3>
                </div>
                <div class="card-body">
                    <table class="table table-sm table-borderless mb-0">
                        <tr>
                            <td class="text-muted">Apertura</td>
                            <td class="text-right" id="cierre-apertura-caja">${{ number_format($totales['apertura_caja'], 0, ',', '.') }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted">+ Ingresos (todos los medios)</td>
                            <td class="text-right text-success" id="cierre-ing-caja">+${{ number_format($totales['total_ingresos'], 0, ',', '.') }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted">- Egresos (todos los medios)</td>
                            <td class="text-right text-danger" id="cierre-egr-caja">-${{ number_format($totales['total_egresos'] - $totales['deposito_banco'], 0, ',', '.') }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted">- Depósito banco</td>
                            <td class="text-right text-danger" id="cierre-deposito">-${{ number_format($totales['deposito_banco'], 0, ',', '.') }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted">- Neto Créd./Déb. <small class="text-muted">(no físico)</small></td>
                            <td class="text-right text-danger" id="cierre-neto-credito">-${{ number_format($totales['neto_credito_debito'], 0, ',', '.') }}</td>
                        </tr>
                        <tr class="border-top font-weight-bold">
                            <td>= Cierre caja</td>
                            <td class="text-right font-weight-bold" id="cierre-caja-valor" style="font-size:1.1rem">
                                ${{ number_format($totales['cierre_caja'], 0, ',', '.') }}
                            </td>
                        </tr>
                    </table>
                </div>
            </div>

            {{-- Cierre Tecnoelectro --}}
            <div class="card card-outline card-primary card-brand-top shadow-sm">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-file-invoice-dollar text-brand mr-1"></i> Cierre Tecnoelectro</h3>
                </div>
                <div class="card-body">
                    <table class="table table-sm table-borderless mb-0">
                        <tr>
                            <td class="text-muted">Apertura</td>
                            <td class="text-right" id="cierre-apertura-tecno">${{ number_format($totales['apertura_tecnoelectro'], 0, ',', '.') }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted">+ Ingresos Tecnoelectro</td>
                            <td class="text-right text-success" id="cierre-ing-tecno">+${{ number_format($totales['ingreso_tecnoelectro'], 0, ',', '.') }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted">- Egresos Tecnoelectro</td>
                            <td class="text-right text-danger" id="cierre-egr-tecno">-${{ number_format($totales['egreso_tecnoelectro'], 0, ',', '.') }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted">- Depósito Banco Tecnoelectro</td>
                            <td class="text-right text-danger" id="cierre-deposito-tecno">-${{ number_format($totales['deposito_banco_tecnoelectro'], 0, ',', '.') }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted">- Neto Créd./Déb. Tecno. <small class="text-muted">(no físico)</small></td>
                            <td class="text-right text-danger" id="cierre-neto-credito-tecno">-${{ number_format($totales['neto_credito_debito_tecno'], 0, ',', '.') }}</td>
                        </tr>
                        <tr class="border-top font-weight-bold">
                            <td>= Cierre Tecnoelectro</td>
                            <td class="text-right font-weight-bold" id="cierre-tecno-valor" style="font-size:1.1rem">
                                ${{ number_format($totales['cierre_tecnoelectro'], 0, ',', '.') }}
                            </td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
    </div>

    {{-- BOTONES CERRAR / REABRIR --}}
    <div class="mb-4 d-flex justify-content-end">
        @if($caja->isAbierta())
            <button class="btn btn-brand btn-lg" id="btn-cerrar-caja">
                <i class="fas fa-lock mr-1"></i> Cerrar caja del día
            </button>
        @else
            @can('flujo_caja.reabrir')
            <button class="btn btn-outline-brand btn-lg" id="btn-reabrir-caja">
                <i class="fas fa-lock-open mr-1"></i> Reabrir caja
            </button>
            @endcan
        @endif
    </div>

</div>{{-- /flujo-caja-app --}}

{{-- Variables JS globales --}}
<script>
    window.FlujoCaja = {
        cajaId:          {{ $caja->id }},
        cajaAbierta:     {{ $caja->isAbierta() ? 'true' : 'false' }},
        urlMovimiento:   '{{ route('flujo_caja.movimiento') }}',
        urlDia:          '{{ route('flujo_caja.dia') }}',
        urlApertura:     '{{ route('flujo_caja.apertura', $caja->id) }}',
        urlCerrar:       '{{ route('flujo_caja.cerrar', $caja->id) }}',
        urlReabrir:      '{{ route('flujo_caja.reabrir', $caja->id) }}',
        urlAnularBase:   '{{ url('flujo-caja/movimiento') }}',
        csrfToken:       '{{ csrf_token() }}',
    };
</script>
@endsection

@push('page_scripts')
<script>
$(document).ready(function () {

    // -----------------------------------------------------------------------
    // Notificaciones Bootstrap (reemplaza toastr)
    // -----------------------------------------------------------------------
    function notify(msg, type) {
        type = type || 'success';
        var icon = type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle';
        var $alert = $(
            '<div class="alert alert-' + type + ' alert-dismissible fade show shadow-sm" ' +
            'style="position:fixed;top:15px;right:15px;z-index:9999;min-width:280px;max-width:420px;">' +
            '<i class="fas ' + icon + ' mr-2"></i>' + msg +
            '<button type="button" class="close" data-dismiss="alert">' +
            '<span>&times;</span></button></div>'
        );
        $('body').append($alert);
        setTimeout(function () { $alert.alert('close'); }, 3500);
    }

    // -----------------------------------------------------------------------
    // Helpers
    // -----------------------------------------------------------------------
    function fmt(n) {
        return '$' + Number(n).toLocaleString('es-CL', {minimumFractionDigits: 0});
    }

    function actualizarResumen(t) {
        // Resumen Caja Chica
        $('#res-ing-efectivo').text(fmt(t.ingreso_efectivo));
        $('#res-egr-efectivo').text('-' + fmt(t.egreso_efectivo));
        $('#res-net-efectivo').text(fmt(t.ingreso_efectivo - t.egreso_efectivo));

        $('#res-ing-credito').text(fmt(t.ingreso_credito_debito));
        $('#res-egr-credito').text('-' + fmt(t.egreso_credito_debito));
        $('#res-net-credito').text(fmt(t.ingreso_credito_debito - t.egreso_credito_debito));

        $('#res-ing-trans').text(fmt(t.ingreso_transferencia));
        $('#res-egr-trans').text('-' + fmt(t.egreso_transferencia));
        $('#res-net-trans').text(fmt(t.ingreso_transferencia - t.egreso_transferencia));

        $('#res-deposito-banco').text('-' + fmt(t.deposito_banco));
        $('#res-total-ing').text('+' + fmt(t.total_ingresos));
        $('#res-total-egr').text('-' + fmt(t.total_egresos));
        $('#res-total-net').text(fmt(t.total_ingresos - t.total_egresos));

        // Resumen Tecnoelectro
        $('#res-ing-efectivo-tecno').text(fmt(t.ingreso_efectivo_tecno));
        $('#res-egr-efectivo-tecno').text('-' + fmt(t.egreso_efectivo_tecno));
        $('#res-net-efectivo-tecno').text(fmt(t.ingreso_efectivo_tecno - t.egreso_efectivo_tecno));

        $('#res-ing-credito-tecno').text(fmt(t.ingreso_credito_debito_tecno));
        $('#res-egr-credito-tecno').text('-' + fmt(t.egreso_credito_debito_tecno));
        $('#res-net-credito-tecno').text(fmt(t.ingreso_credito_debito_tecno - t.egreso_credito_debito_tecno));

        $('#res-ing-devolucion-abono').text(fmt(t.ingreso_devolucion_abono));
        $('#res-egr-devolucion-abono').text('-' + fmt(t.egreso_devolucion_abono));
        $('#res-net-devolucion-abono').text(fmt(t.ingreso_devolucion_abono - t.egreso_devolucion_abono));

        $('#res-deposito-banco-tecno').text('-' + fmt(t.deposito_banco_tecnoelectro));
        $('#res-total-ing-tecno').text('+' + fmt(t.total_ingresos_tecno));
        $('#res-total-egr-tecno').text('-' + fmt(t.total_egresos_tecno));
        $('#res-total-net-tecno').text(fmt(t.total_ingresos_tecno - t.total_egresos_tecno));

        // Cierre caja chica
        $('#cierre-apertura-caja').text(fmt(t.apertura_caja));
        $('#cierre-ing-caja').text('+' + fmt(t.total_ingresos));
        $('#cierre-egr-caja').text('-' + fmt(t.total_egresos - t.deposito_banco));
        $('#cierre-deposito').text('-' + fmt(t.deposito_banco));
        $('#cierre-neto-credito').text('-' + fmt(t.neto_credito_debito));
        $('#cierre-caja-valor').text(fmt(t.cierre_caja));

        // Cierre Tecnoelectro
        $('#cierre-apertura-tecno').text(fmt(t.apertura_tecnoelectro));
        $('#cierre-ing-tecno').text('+' + fmt(t.ingreso_tecnoelectro));
        $('#cierre-egr-tecno').text('-' + fmt(t.egreso_tecnoelectro));
        $('#cierre-deposito-tecno').text('-' + fmt(t.deposito_banco_tecnoelectro));
        $('#cierre-neto-credito-tecno').text('-' + fmt(t.neto_credito_debito_tecno));
        $('#cierre-tecno-valor').text(fmt(t.cierre_tecnoelectro));
    }

    var mediosTecno = ['efectivo_tecno', 'credito_debito_tecno', 'devolucion_abono', 'deposito_banco_tecnoelectro'];

    function agregarFilaMovimiento(m) {
        var esTecno = mediosTecno.indexOf(m.medio) !== -1;
        var tbodyId = esTecno ? '#tbody-movimientos-tecno' : '#tbody-movimientos';
        var filaCero = esTecno ? '#fila-vacia-tecno' : '#fila-vacia';

        $(filaCero).remove();

        var badgeTipo = m.tipo_movimiento === 'ingreso'
            ? '<span class="badge badge-success">Ingreso</span>'
            : '<span class="badge badge-danger">Egreso</span>';
        var signo    = m.tipo_movimiento === 'ingreso' ? '+' : '-';
        var classAmt = m.tipo_movimiento === 'ingreso' ? 'text-success font-weight-bold' : 'text-danger font-weight-bold';
        var btnAnular = FlujoCaja.cajaAbierta
            ? '<button class="btn btn-xs btn-outline-danger btn-anular" data-id="' + m.id + '" title="Anular"><i class="fas fa-ban"></i></button>'
            : '';
        var extra = FlujoCaja.cajaAbierta ? '<td>' + btnAnular + '</td>' : '';

        $(tbodyId).append(
            '<tr id="fila-' + m.id + '">' +
            '<td>' + m.created_at + '</td>' +
            '<td>' + badgeTipo + '</td>' +
            '<td>' + m.medio_label + '</td>' +
            '<td>' + (m.detalle || '—') + '</td>' +
            '<td class="text-right ' + classAmt + '">' + signo + fmt(m.monto) + '</td>' +
            '<td>' + m.usuario + '</td>' +
            '<td><span class="badge badge-success">Activo</span></td>' +
            extra +
            '</tr>'
        );
    }

    // -----------------------------------------------------------------------
    // Cambio de fecha
    // -----------------------------------------------------------------------
    $('#selector-fecha').on('change', function () {
        var fecha = $(this).val();
        window.location.href = '{{ route('flujo_caja.index') }}?fecha=' + fecha;
    });

    // -----------------------------------------------------------------------
    // Guardar apertura
    // -----------------------------------------------------------------------
    $('.btn-guardar-apertura').on('click', function () {
        var campo = $(this).data('campo');
        var valor = campo === 'apertura_caja'
            ? $('#apertura-caja').val()
            : $('#apertura-tecnoelectro').val();

        var data = {};
        data[campo] = valor;
        data['_method'] = 'PATCH';

        $.ajax({
            url: FlujoCaja.urlApertura,
            method: 'POST',
            data: Object.assign(data, {_token: FlujoCaja.csrfToken}),
            success: function (res) {
                if (res.success) {
                    actualizarResumen(res.totales);
                    notify('Apertura actualizada.');
                }
            },
            error: function (xhr) {
                notify(xhr.responseJSON?.error || 'Error al actualizar apertura.', 'danger');
            }
        });
    });

    // -----------------------------------------------------------------------
    // Registrar movimiento
    // -----------------------------------------------------------------------
    $('#form-movimiento').on('submit', function (e) {
        e.preventDefault();
        var formData = $(this).serialize() + '&_token=' + FlujoCaja.csrfToken;

        $.ajax({
            url: FlujoCaja.urlMovimiento,
            method: 'POST',
            data: formData,
            success: function (res) {
                if (res.success) {
                    agregarFilaMovimiento(res.movimiento);
                    actualizarResumen(res.totales);
                    $('#form-movimiento')[0].reset();
                    notify('Movimiento registrado.');
                }
            },
            error: function (xhr) {
                notify(xhr.responseJSON?.error || 'Error al registrar movimiento.', 'danger');
            }
        });
    });

    // -----------------------------------------------------------------------
    // Anular movimiento
    // -----------------------------------------------------------------------
    $(document).on('click', '.btn-anular', function () {
        var id  = $(this).data('id');
        var fila = '#fila-' + id;

        if (!confirm('¿Anular este movimiento? Esta acción no se puede deshacer.')) return;
        console.log(FlujoCaja.urlAnularBase + '/' + id + '/anular');
        $.ajax({
            url: FlujoCaja.urlAnularBase + '/' + id + '/anular',
            method: 'POST',
            data: {_token: FlujoCaja.csrfToken},
            success: function (res) {
                if (res.success) {
                    // Marcar fila como anulada
                    $(fila).addClass('text-muted');
                    $(fila).find('.badge-success, .badge-danger').first()
                           .removeClass('badge-success badge-danger')
                           .addClass('badge-secondary');
                    $(fila).find('.text-success.font-weight-bold, .text-danger.font-weight-bold')
                           .removeClass('text-success text-danger font-weight-bold');
                    $(fila).find('td:nth-child(7)').html('<span class="badge badge-secondary">Anulado</span>');
                    $(fila).find('.btn-anular').remove();

                    actualizarResumen(res.totales);
                    notify('Movimiento anulado.');
                }
            },
            error: function (xhr) {
                notify(xhr.responseJSON?.error || 'Error al anular movimiento.', 'danger');
            }
        });
    });

    // -----------------------------------------------------------------------
    // Cerrar caja
    // -----------------------------------------------------------------------
    $('#btn-cerrar-caja').on('click', function () {
        if (!confirm('¿Cerrar la caja del día? Se persistirán los cierres calculados.')) return;

        $.ajax({
            url: FlujoCaja.urlCerrar,
            method: 'POST',
            data: {_token: FlujoCaja.csrfToken},
            success: function (res) {
                if (res.success) {
                    actualizarResumen(res.totales);
                    notify('Caja cerrada correctamente.');
                    setTimeout(function () { location.reload(); }, 800);
                }
            },
            error: function (xhr) {
                notify(xhr.responseJSON?.error || 'Error al cerrar la caja.', 'danger');
            }
        });
    });

    // -----------------------------------------------------------------------
    // Reabrir caja
    // -----------------------------------------------------------------------
    $('#btn-reabrir-caja').on('click', function () {
        if (!confirm('¿Reabrir la caja del día?')) return;

        $.ajax({
            url: FlujoCaja.urlReabrir,
            method: 'POST',
            data: {_token: FlujoCaja.csrfToken},
            success: function (res) {
                if (res.success) {
                    notify('Caja reabierta.');
                    setTimeout(function () { location.reload(); }, 800);
                }
            },
            error: function (xhr) {
                notify(xhr.responseJSON?.error || 'Error al reabrir la caja.', 'danger');
            }
        });
    });

});
</script>
@endpush
