@extends('layouts.app')
@section('title', 'INFORMES FLUJO DE CAJA')

@push('page_css')
<style>
    .viz-root {
        --series-ingreso: #2a78d6;
        --series-egreso:  #eb6834;
        --series-neto:    #4a3aa7;
        --series-caja:    #2a78d6;
        --series-tecno:   #eb6834;
        --series-tbk:     #1baf7a;
    }
    .viz-root .chart-box { position: relative; height: 300px; }
    .viz-root .chart-box-lg { position: relative; height: 360px; }
    .kpi-value { font-size: 1.5rem; font-weight: 600; line-height: 1.2; }
    .kpi-label { font-size: .75rem; text-transform: uppercase; letter-spacing: .04em; }
</style>
@endpush

@section('content')
<section class="content-header">
    <div class="container-fluid">
        <div class="row mb-2 align-items-center">
            <div class="col-sm-6">
                <h1><i class="fas fa-chart-line text-brand mr-2"></i>Informes de Flujo de Caja</h1>
            </div>
            <div class="col-sm-6 d-flex justify-content-end">
                <a href="{{ route('flujo_caja.index') }}" class="btn btn-outline-brand">
                    <i class="fas fa-arrow-left mr-1"></i> Volver a Flujo de Caja
                </a>
            </div>
        </div>
    </div>
</section>

<div class="content px-3 viz-root">

    {{-- Filtro de rango --}}
    <div class="card card-outline card-primary card-brand-top shadow-sm mb-3">
        <div class="card-body py-3">
            <form method="GET" action="{{ route('flujo_caja.informes') }}" class="form-row align-items-end">
                <div class="col-auto">
                    <label for="desde" class="mb-1 small font-weight-bold">Desde</label>
                    <input type="date" id="desde" name="desde" class="form-control"
                           value="{{ $desde->toDateString() }}"
                           max="{{ \Carbon\Carbon::today()->toDateString() }}">
                </div>
                <div class="col-auto">
                    <label for="hasta" class="mb-1 small font-weight-bold">Hasta</label>
                    <input type="date" id="hasta" name="hasta" class="form-control"
                           value="{{ $hasta->toDateString() }}"
                           max="{{ \Carbon\Carbon::today()->toDateString() }}">
                </div>
                <div class="col-auto">
                    <button type="submit" class="btn btn-brand">
                        <i class="fas fa-filter mr-1"></i> Aplicar
                    </button>
                </div>
                <div class="col text-right">
                    <span class="text-muted small">
                        Agrupado por {{ $agrupacion === 'mes' ? 'mes' : 'día' }}
                    </span>
                </div>
            </form>
        </div>
    </div>

    @if (empty($etiquetas))
        <div class="alert alert-info">
            <i class="fas fa-info-circle mr-1"></i>
            No hay movimientos registrados en el rango seleccionado.
        </div>
    @else

    {{-- KPIs --}}
    <div class="row" id="kpi-row"></div>

    {{-- Gráficos por categoría --}}
    <div class="row">
        <div class="col-lg-4 mb-3">
            <div class="card card-outline card-primary card-brand-top shadow-sm h-100">
                <div class="card-header py-2"><h3 class="card-title"><i class="fas fa-cash-register text-brand mr-2"></i>Caja Chica</h3></div>
                <div class="card-body"><div class="chart-box"><canvas id="chart-caja-chica"></canvas></div></div>
            </div>
        </div>
        <div class="col-lg-4 mb-3">
            <div class="card card-outline card-primary card-brand-top shadow-sm h-100">
                <div class="card-header py-2"><h3 class="card-title"><i class="fas fa-store text-brand mr-2"></i>Tecnoelectro</h3></div>
                <div class="card-body"><div class="chart-box"><canvas id="chart-tecnoelectro"></canvas></div></div>
            </div>
        </div>
        <div class="col-lg-4 mb-3">
            <div class="card card-outline card-primary card-brand-top shadow-sm h-100">
                <div class="card-header py-2"><h3 class="card-title"><i class="fas fa-credit-card text-brand mr-2"></i>Transbank</h3></div>
                <div class="card-body"><div class="chart-box"><canvas id="chart-transbank"></canvas></div></div>
            </div>
        </div>
    </div>

    {{-- Comparativo y totales --}}
    <div class="row">
        <div class="col-lg-6 mb-3">
            <div class="card card-outline card-primary card-brand-top shadow-sm h-100">
                <div class="card-header py-2">
                    <h3 class="card-title"><i class="fas fa-layer-group text-brand mr-2"></i>Comparativo por categoría (neto)</h3>
                </div>
                <div class="card-body">
                    <div class="chart-box-lg"><canvas id="chart-comparativo"></canvas></div>
                    <p class="text-muted small mb-0 mt-2">Neto = ingresos − egresos de cada categoría.</p>
                </div>
            </div>
        </div>
        <div class="col-lg-6 mb-3">
            <div class="card card-outline card-primary card-brand-top shadow-sm h-100">
                <div class="card-header py-2">
                    <h3 class="card-title"><i class="fas fa-scale-balanced text-brand mr-2"></i>Totales sumados</h3>
                </div>
                <div class="card-body">
                    <div class="chart-box-lg"><canvas id="chart-totales"></canvas></div>
                    <p class="text-muted small mb-0 mt-2">Ingresos y egresos de las tres categorías sumadas, por período.</p>
                </div>
            </div>
        </div>
    </div>

    {{-- Neto acumulado --}}
    <div class="row">
        <div class="col-12 mb-3">
            <div class="card card-outline card-primary card-brand-top shadow-sm">
                <div class="card-header py-2">
                    <h3 class="card-title"><i class="fas fa-chart-area text-brand mr-2"></i>Neto acumulado del período</h3>
                </div>
                <div class="card-body">
                    <div class="chart-box"><canvas id="chart-acumulado"></canvas></div>
                    <p class="text-muted small mb-0 mt-2">
                        Suma corrida de (ingresos − egresos) de las tres categorías. Muestra si el período va ganando o perdiendo.
                    </p>
                </div>
            </div>
        </div>
    </div>

    {{-- Depósitos al banco --}}
    <div class="row">
        <div class="col-12 mb-3">
            <div class="card card-outline card-primary card-brand-top shadow-sm">
                <div class="card-header py-2">
                    <h3 class="card-title"><i class="fas fa-university text-brand mr-2"></i>Depósitos al banco</h3>
                </div>
                <div class="card-body">
                    <div class="chart-box"><canvas id="chart-depositos"></canvas></div>
                    <p class="text-muted small mb-0 mt-2">
                        <i class="fas fa-info-circle mr-1"></i>
                        Los depósitos al banco se muestran aquí por separado y <strong>no están incluidos</strong>
                        en los egresos de Caja Chica ni Tecnoelectro de los gráficos anteriores.
                        En la vista de Flujo de Caja sí se descuentan del cierre diario.
                    </p>
                </div>
            </div>
        </div>
    </div>

    @endif
</div>
@endsection

@push('page_scripts')
@if (!empty($etiquetas))
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
(function () {
    const etiquetas = @json($etiquetas);
    const series    = @json($series);
    const depositos = @json($depositos);

    const css = getComputedStyle(document.querySelector('.viz-root'));
    const color = n => css.getPropertyValue('--series-' + n).trim();

    const clp = v => '$' + Math.round(v).toLocaleString('es-CL');
    const suma = arr => arr.reduce((a, b) => a + b, 0);
    const restar = (a, b) => a.map((v, i) => v - b[i]);

    Chart.defaults.font.family = 'inherit';
    Chart.defaults.maintainAspectRatio = false;

    const ejeMonto = {
        beginAtZero: true,
        border: { display: false },
        grid: { color: 'rgba(0,0,0,.06)' },
        ticks: { callback: v => clp(v) }
    };
    const ejeX = { grid: { display: false }, border: { display: false } };

    const tooltip = {
        callbacks: { label: c => c.dataset.label + ': ' + clp(c.parsed.y) }
    };

    // Barras ingreso/egreso + línea de neto. Todo en pesos: un solo eje Y.
    function graficoCategoria(canvasId, datos) {
        const neto = restar(datos.ingreso, datos.egreso);
        return new Chart(document.getElementById(canvasId), {
            data: {
                labels: etiquetas,
                datasets: [
                    { type: 'bar', label: 'Ingresos', data: datos.ingreso,
                      backgroundColor: color('ingreso'), borderRadius: 4, borderSkipped: 'bottom' },
                    { type: 'bar', label: 'Egresos', data: datos.egreso,
                      backgroundColor: color('egreso'), borderRadius: 4, borderSkipped: 'bottom' },
                    { type: 'line', label: 'Neto', data: neto,
                      borderColor: color('neto'), backgroundColor: color('neto'),
                      borderWidth: 2, pointRadius: 3, pointHoverRadius: 6, tension: .25 }
                ]
            },
            options: {
                interaction: { mode: 'index', intersect: false },
                scales: { x: ejeX, y: ejeMonto },
                plugins: {
                    legend: { position: 'bottom', labels: { boxWidth: 12, usePointStyle: true } },
                    tooltip: tooltip
                }
            }
        });
    }

    graficoCategoria('chart-caja-chica',   series.caja_chica);
    graficoCategoria('chart-tecnoelectro', series.tecnoelectro);
    graficoCategoria('chart-transbank',    series.transbank);

    // Comparativo: solo el neto de cada categoría — 6 líneas serían ilegibles.
    new Chart(document.getElementById('chart-comparativo'), {
        type: 'line',
        data: {
            labels: etiquetas,
            datasets: [
                { label: 'Caja Chica', data: restar(series.caja_chica.ingreso, series.caja_chica.egreso),
                  borderColor: color('caja'), backgroundColor: color('caja') },
                { label: 'Tecnoelectro', data: restar(series.tecnoelectro.ingreso, series.tecnoelectro.egreso),
                  borderColor: color('tecno'), backgroundColor: color('tecno') },
                { label: 'Transbank', data: restar(series.transbank.ingreso, series.transbank.egreso),
                  borderColor: color('tbk'), backgroundColor: color('tbk') }
            ].map(d => ({ ...d, borderWidth: 2, pointRadius: 3, pointHoverRadius: 6, tension: .25 }))
        },
        options: {
            interaction: { mode: 'index', intersect: false },
            scales: { x: ejeX, y: { ...ejeMonto, beginAtZero: false } },
            plugins: {
                legend: { position: 'bottom', labels: { boxWidth: 12, usePointStyle: true } },
                tooltip: tooltip
            }
        }
    });

    // Totales: suma de las 3 categorías + neto acumulado.
    const totIngreso = etiquetas.map((_, i) =>
        series.caja_chica.ingreso[i] + series.tecnoelectro.ingreso[i] + series.transbank.ingreso[i]);
    const totEgreso = etiquetas.map((_, i) =>
        series.caja_chica.egreso[i] + series.tecnoelectro.egreso[i] + series.transbank.egreso[i]);

    let acum = 0;
    const netoAcumulado = totIngreso.map((v, i) => acum += (v - totEgreso[i]));

    new Chart(document.getElementById('chart-totales'), {
        type: 'bar',
        data: {
            labels: etiquetas,
            datasets: [
                { label: 'Ingresos', data: totIngreso,
                  backgroundColor: color('ingreso'), borderRadius: 4, borderSkipped: 'bottom' },
                { label: 'Egresos', data: totEgreso,
                  backgroundColor: color('egreso'), borderRadius: 4, borderSkipped: 'bottom' }
            ]
        },
        options: {
            interaction: { mode: 'index', intersect: false },
            scales: { x: ejeX, y: ejeMonto },
            plugins: {
                legend: { position: 'bottom', labels: { boxWidth: 12, usePointStyle: true } },
                tooltip: tooltip
            }
        }
    });

    // El acumulado va en su propio gráfico: su escala (millones) aplasta las
    // barras diarias si comparten eje, y un segundo eje Y sería peor.
    new Chart(document.getElementById('chart-acumulado'), {
        type: 'line',
        data: {
            labels: etiquetas,
            datasets: [{
                label: 'Neto acumulado', data: netoAcumulado,
                borderColor: color('neto'), backgroundColor: color('neto'),
                borderWidth: 2, pointRadius: 3, pointHoverRadius: 6, tension: .25, fill: false
            }]
        },
        options: {
            interaction: { mode: 'index', intersect: false },
            scales: { x: ejeX, y: { ...ejeMonto, beginAtZero: false } },
            plugins: { legend: { display: false }, tooltip: tooltip }
        }
    });

    // Depósitos al banco: apilados porque suman a un total que interesa.
    new Chart(document.getElementById('chart-depositos'), {
        type: 'bar',
        data: {
            labels: etiquetas,
            datasets: [
                { label: 'Depósito Caja Chica', data: depositos.caja_chica,
                  backgroundColor: color('caja'), borderRadius: 4, borderSkipped: 'bottom',
                  borderColor: '#fff', borderWidth: { top: 2 } },
                { label: 'Depósito Tecnoelectro', data: depositos.tecnoelectro,
                  backgroundColor: color('tecno'), borderRadius: 4, borderSkipped: 'bottom',
                  borderColor: '#fff', borderWidth: { top: 2 } }
            ]
        },
        options: {
            interaction: { mode: 'index', intersect: false },
            scales: { x: { ...ejeX, stacked: true }, y: { ...ejeMonto, stacked: true } },
            plugins: {
                legend: { position: 'bottom', labels: { boxWidth: 12, usePointStyle: true } },
                tooltip: tooltip
            }
        }
    });

    // KPIs del período
    const kpis = [
        { label: 'Ingresos totales', valor: suma(totIngreso), color: color('ingreso') },
        { label: 'Egresos totales',  valor: suma(totEgreso),  color: color('egreso') },
        { label: 'Neto del período', valor: suma(totIngreso) - suma(totEgreso), color: color('neto') },
        { label: 'Depósitos al banco',
          valor: suma(depositos.caja_chica) + suma(depositos.tecnoelectro), color: color('tbk') }
    ];

    document.getElementById('kpi-row').innerHTML = kpis.map(k => `
        <div class="col-md-3 col-sm-6 mb-3">
            <div class="card card-outline card-primary card-brand-top shadow-sm h-100">
                <div class="card-body py-3">
                    <div class="kpi-label text-muted">${k.label}</div>
                    <div class="kpi-value" style="color:${k.color}">${clp(k.valor)}</div>
                </div>
            </div>
        </div>`).join('');
})();
</script>
@endif
@endpush
