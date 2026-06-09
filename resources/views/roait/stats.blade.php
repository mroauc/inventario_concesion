<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Stats – ROAVAL</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.4/dist/chart.umd.min.js"></script>
    <style>
        body { background: #0f172a; color: #e2e8f0; font-family: 'Segoe UI', sans-serif; }
        .card-dark { background: #1e293b; border: 1px solid #334155; border-radius: 12px; }
        .stat-number { font-size: 2.5rem; font-weight: 700; color: #38bdf8; }
        .stat-label { color: #94a3b8; font-size: 0.85rem; text-transform: uppercase; letter-spacing: 0.05em; }
        .badge-pagina { font-size: 0.75rem; }
        table { color: #cbd5e1; }
        th { color: #94a3b8; font-weight: 500; font-size: 0.8rem; text-transform: uppercase; letter-spacing: 0.04em; }
        a { color: #38bdf8; }
        .page-bar { height: 8px; border-radius: 4px; background: #334155; overflow: hidden; }
        .page-bar-fill { height: 100%; border-radius: 4px; transition: width 0.6s ease; }
    </style>
</head>
<body class="p-4">
<div class="container-fluid">

    {{-- Header --}}
    <div class="d-flex align-items-center mb-4 gap-3">
        <div>
            <h1 class="h4 mb-0 fw-bold">📊 Estadísticas de Visitas</h1>
            <p class="text-secondary mb-0" style="font-size:0.85rem;">Landing — serviciotecnicoroaval.com</p>
        </div>
        <span class="ms-auto text-secondary" style="font-size:0.8rem;">{{ now()->format('d/m/Y H:i') }}</span>
    </div>

    {{-- KPIs --}}
    <div class="row g-3 mb-4">
        <div class="col-6 col-md-3">
            <div class="card-dark p-3 text-center">
                <div class="stat-number">{{ number_format($totalGeneral) }}</div>
                <div class="stat-label">Total visitas</div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card-dark p-3 text-center">
                <div class="stat-number">{{ number_format($hoy) }}</div>
                <div class="stat-label">Hoy</div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card-dark p-3 text-center">
                <div class="stat-number">{{ number_format($semana) }}</div>
                <div class="stat-label">Esta semana</div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card-dark p-3 text-center">
                <div class="stat-number">{{ count($paginas) }}</div>
                <div class="stat-label">Páginas tracked</div>
            </div>
        </div>
    </div>

    <div class="row g-3 mb-4">
        {{-- Visitas por página --}}
        <div class="col-md-4">
            <div class="card-dark p-3 h-100">
                <h6 class="text-secondary mb-3">Visitas por página</h6>
                @foreach($paginas as $p)
                    @php $cnt = $totalesPorPagina[$p] ?? 0; $pct = $totalGeneral > 0 ? round($cnt / $totalGeneral * 100) : 0; @endphp
                    <div class="mb-3">
                        <div class="d-flex justify-content-between mb-1">
                            <span class="text-capitalize">{{ $p }}</span>
                            <span class="text-info fw-bold">{{ number_format($cnt) }} <small class="text-secondary">({{ $pct }}%)</small></span>
                        </div>
                        <div class="page-bar">
                            <div class="page-bar-fill" style="width:{{ $pct }}%; background:#38bdf8;"></div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        {{-- Gráfico últimos 30 días --}}
        <div class="col-md-8">
            <div class="card-dark p-3 h-100">
                <h6 class="text-secondary mb-3">Visitas diarias — últimos 30 días</h6>
                <canvas id="chartDiario" height="120"></canvas>
            </div>
        </div>
    </div>

    <div class="row g-3 mb-4">
        {{-- Top referrers --}}
        <div class="col-md-5">
            <div class="card-dark p-3 h-100">
                <h6 class="text-secondary mb-3">Top referrers</h6>
                @if($topReferrers->isEmpty())
                    <p class="text-secondary small">Sin datos aún.</p>
                @else
                <table class="table table-sm table-borderless mb-0">
                    <thead><tr><th>Referrer</th><th class="text-end">Visitas</th></tr></thead>
                    <tbody>
                    @foreach($topReferrers as $r)
                        <tr>
                            <td style="max-width:260px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">
                                <small>{{ $r->referrer ?: '(directo)' }}</small>
                            </td>
                            <td class="text-end"><span class="text-info">{{ $r->total }}</span></td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
                @endif
            </div>
        </div>

        {{-- Últimas visitas --}}
        <div class="col-md-7">
            <div class="card-dark p-3 h-100">
                <h6 class="text-secondary mb-3">Últimas 20 visitas</h6>
                <div style="max-height:280px;overflow-y:auto;">
                <table class="table table-sm table-borderless mb-0">
                    <thead><tr><th>Fecha</th><th>Página</th><th>IP</th><th>Referrer</th></tr></thead>
                    <tbody>
                    @foreach($ultimas as $v)
                        <tr>
                            <td><small class="text-secondary">{{ $v->created_at->format('d/m H:i') }}</small></td>
                            <td><span class="badge bg-info bg-opacity-20 text-info badge-pagina">{{ $v->pagina }}</span></td>
                            <td><small>{{ $v->ip }}</small></td>
                            <td style="max-width:180px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">
                                <small class="text-secondary">{{ $v->referrer ?: '—' }}</small>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
                </div>
            </div>
        </div>
    </div>

</div>

<script>
const diasData = @json($visitasPorDia);
const labels = diasData.map(d => d.fecha);
const totales = diasData.map(d => d.total);

new Chart(document.getElementById('chartDiario'), {
    type: 'bar',
    data: {
        labels,
        datasets: [{
            label: 'Visitas',
            data: totales,
            backgroundColor: 'rgba(56,189,248,0.35)',
            borderColor: '#38bdf8',
            borderWidth: 1,
            borderRadius: 4,
        }]
    },
    options: {
        responsive: true,
        plugins: { legend: { display: false } },
        scales: {
            x: { ticks: { color: '#64748b', font: { size: 10 } }, grid: { color: '#1e293b' } },
            y: { ticks: { color: '#64748b' }, grid: { color: '#334155' }, beginAtZero: true }
        }
    }
});
</script>
</body>
</html>
