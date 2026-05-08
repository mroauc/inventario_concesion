@extends('layouts.app')
@section('title', 'Flujo de Caja')

@push('page_css')
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=JetBrains+Mono:wght@500;600;700&display=swap" rel="stylesheet">
<style>
/* ─── Design tokens ─────────────────────────────────────────────── */
:root {
  --brand:       #132a56;
  --brand-2:     #1a3a72;
  --brand-soft:  #eef1f8;
  --ink:         #0f172a;
  --ink-2:       #334155;
  --ink-3:       #64748b;
  --ink-4:       #94a3b8;
  --line:        #e2e8f0;
  --line-2:      #eef1f5;
  --bg:          #f4f6fa;
  --ok:          #0ea968;
  --ok-soft:     #e6f7ef;
  --bad:         #dc2a3a;
  --bad-soft:    #fdecee;
  --warn:        #c47f0f;
  --warn-soft:   #fff5e0;
  --info:        #0b6cc4;
  --info-soft:   #e6f1fb;
  --violet:      #5b3fb1;
  --violet-soft: #efebfb;
}

/* ─── Base ──────────────────────────────────────────────────────── */
#flujo-caja-app { font-family: 'Inter', system-ui, -apple-system, Segoe UI, Roboto, sans-serif; }
.num { font-family: 'JetBrains Mono', ui-monospace, Menlo, monospace; font-variant-numeric: tabular-nums; letter-spacing: -0.01em; }

/* ─── Page header ───────────────────────────────────────────────── */
.page-shell { padding: 18px 22px 110px; }
.page-head { display: flex; align-items: center; justify-content: space-between; gap: 16px; flex-wrap: wrap; margin-bottom: 18px; }
.page-title { display: flex; align-items: center; gap: 14px; }
.page-title .ico { width: 44px; height: 44px; border-radius: 12px; background: linear-gradient(135deg, var(--brand) 0%, var(--brand-2) 100%); color: #fff; display: flex; align-items: center; justify-content: center; font-size: 18px; box-shadow: 0 8px 22px rgba(19,42,86,.25); flex-shrink: 0; }
.page-title h1 { font-size: 22px; font-weight: 700; margin: 0; letter-spacing: -0.01em; color: var(--ink); }
.page-title .crumb { font-size: 12px; color: var(--ink-3); font-weight: 500; display: flex; gap: 6px; align-items: center; }
.page-title .crumb i { font-size: 8px; color: var(--ink-4); }

.head-actions { display: flex; align-items: center; gap: 10px; flex-wrap: wrap; }

/* estado pill */
.estado-pill { display: inline-flex; align-items: center; gap: 8px; padding: 8px 14px; border-radius: 999px; font-size: 12.5px; font-weight: 600; background: var(--ok-soft); color: var(--ok); border: 1px solid #c5ecd9; white-space: nowrap; }
.estado-pill .dot { width: 8px; height: 8px; border-radius: 50%; background: var(--ok); box-shadow: 0 0 0 4px rgba(14,169,104,.18); animation: fc-pulse 1.8s ease-in-out infinite; }
.estado-pill.cerrada { background: #f1f5f9; color: var(--ink-3); border-color: #e2e8f0; }
.estado-pill.cerrada .dot { background: var(--ink-4); box-shadow: none; animation: none; }
@keyframes fc-pulse { 0%,100%{ box-shadow: 0 0 0 4px rgba(14,169,104,.18); } 50%{ box-shadow: 0 0 0 8px rgba(14,169,104,0); } }

/* date picker */
.date-pick { display: flex; align-items: center; background: #fff; border: 1px solid var(--line); border-radius: 10px; overflow: hidden; height: 40px; }
.date-pick .nav-day { padding: 0 10px; height: 100%; background: transparent; border: 0; color: var(--ink-3); cursor: pointer; }
.date-pick .nav-day:hover { background: var(--brand-soft); color: var(--brand); }
.date-pick .nav-day:disabled { opacity: .4; cursor: default; pointer-events: none; }
.date-pick input[type=date] { border: 0; height: 100%; padding: 0 10px; font-size: 13px; font-weight: 600; color: var(--ink); background: transparent; outline: none; font-family: 'Inter', inherit; }
.date-pick .ic { padding: 0 10px 0 6px; color: var(--ink-4); font-size: 13px; }

.btn-ghost { background: #fff; border: 1px solid var(--line); color: var(--ink-2); padding: 0 14px; height: 40px; border-radius: 10px; font-weight: 600; font-size: 13px; display: inline-flex; align-items: center; gap: 8px; text-decoration: none; }
.btn-ghost:hover { border-color: var(--brand); color: var(--brand); background: var(--brand-soft); text-decoration: none; }

/* ─── KPI row ───────────────────────────────────────────────────── */
.kpi-row { display: grid; grid-template-columns: repeat(4, 1fr); gap: 14px; margin-bottom: 18px; }
@media(max-width:1100px){ .kpi-row { grid-template-columns: repeat(2,1fr); } }
.kpi { background: #fff; border: 1px solid var(--line); border-radius: 14px; padding: 16px 18px; position: relative; overflow: hidden; transition: .15s ease; }
.kpi:hover { transform: translateY(-1px); box-shadow: 0 8px 24px rgba(15,23,42,.06); }
.kpi .accent { position: absolute; left: 0; top: 0; bottom: 0; width: 3px; }
.kpi .kpi-label { display: flex; align-items: center; gap: 8px; font-size: 11.5px; font-weight: 600; letter-spacing: .08em; text-transform: uppercase; color: var(--ink-3); margin-bottom: 8px; }
.kpi .kpi-label .ic { width: 22px; height: 22px; border-radius: 6px; display: flex; align-items: center; justify-content: center; font-size: 11px; }
.kpi .kpi-value { font-size: 24px; font-weight: 700; color: var(--ink); letter-spacing: -0.02em; }
.kpi .kpi-sub { font-size: 11.5px; color: var(--ink-3); margin-top: 4px; }

.kpi.k-apertura .accent { background: var(--brand); }
.kpi.k-apertura .ic { background: var(--brand-soft); color: var(--brand); }
.kpi.k-tecno .accent { background: var(--info); }
.kpi.k-tecno .ic { background: var(--info-soft); color: var(--info); }
.kpi.k-ing .accent { background: var(--ok); }
.kpi.k-ing .ic { background: var(--ok-soft); color: var(--ok); }
.kpi.k-egr .accent { background: var(--bad); }
.kpi.k-egr .ic { background: var(--bad-soft); color: var(--bad); }

.kpi-editor { display: flex; align-items: center; gap: 6px; margin-top: 10px; }
.kpi-editor .currency { display: flex; align-items: center; background: #f8fafc; border: 1px solid var(--line); border-radius: 8px; padding: 0 10px; height: 34px; flex: 1; }
.kpi-editor .currency span { color: var(--ink-3); font-weight: 600; font-size: 13px; margin-right: 4px; }
.kpi-editor .currency input { border: 0; background: transparent; outline: none; font-family: 'JetBrains Mono', monospace; font-weight: 600; font-size: 14px; width: 100%; color: var(--ink); }
.kpi-editor .save-btn { height: 34px; width: 34px; border: 0; border-radius: 8px; background: var(--brand); color: #fff; display: flex; align-items: center; justify-content: center; font-size: 12px; cursor: pointer; flex-shrink: 0; }
.kpi-editor .save-btn:hover { background: var(--brand-2); }
.kpi-editor .save-btn:disabled { opacity: .5; cursor: default; }

/* ─── Main grid ─────────────────────────────────────────────────── */
.main-grid { display: grid; grid-template-columns: 1fr 380px; gap: 18px; align-items: start; }
@media(max-width:1180px){ .main-grid { grid-template-columns: 1fr; } }

/* ─── vcard ─────────────────────────────────────────────────────── */
.vcard { background: #fff; border: 1px solid var(--line); border-radius: 14px; overflow: hidden; margin-bottom: 16px; }
.vcard-head { display: flex; align-items: center; justify-content: space-between; padding: 14px 18px; border-bottom: 1px solid var(--line-2); }
.vcard-head.no-border { border-bottom: 0; padding-bottom: 0; }
.vcard-title { display: flex; align-items: center; gap: 10px; font-weight: 700; font-size: 14px; color: var(--ink); }
.vcard-title .tic { width: 28px; height: 28px; border-radius: 8px; display: flex; align-items: center; justify-content: center; font-size: 12px; flex-shrink: 0; }
.vcard-title.t-form   .tic { background: var(--brand-soft); color: var(--brand); }
.vcard-title.t-list   .tic { background: #f1f5f9; color: var(--ink-2); }
.vcard-title.t-cc     .tic { background: var(--ok-soft); color: var(--ok); }
.vcard-title.t-tecno  .tic { background: var(--info-soft); color: var(--info); }
.vcard-title.t-tb     .tic { background: var(--warn-soft); color: var(--warn); }
.vcard-title.t-resumen .tic { background: var(--violet-soft); color: var(--violet); }
.vcard-title small { font-weight: 500; color: var(--ink-3); font-size: 11.5px; letter-spacing: 0; text-transform: none; margin-left: 4px; }

.vcard-tools { display: flex; align-items: center; gap: 6px; }
.vcard-tools .chip { font-size: 11px; font-weight: 600; padding: 3px 10px; border-radius: 999px; background: var(--brand-soft); color: var(--brand); }
.vcard-tools .chip.muted { background: #f1f5f9; color: var(--ink-3); }
.vcard-tools .ico-btn { width: 28px; height: 28px; border: 0; border-radius: 8px; background: transparent; color: var(--ink-3); display: flex; align-items: center; justify-content: center; font-size: 12px; cursor: pointer; }
.vcard-tools .ico-btn:hover { background: var(--brand-soft); color: var(--brand); }
.vcard-body { padding: 16px 18px; }
.vcard-body.flush { padding: 0; }

/* ─── Movement form ─────────────────────────────────────────────── */
.mv-form { display: grid; grid-template-columns: 140px 1fr 160px 1fr auto; gap: 10px; align-items: end; }
@media(max-width:980px){ .mv-form { grid-template-columns: 1fr 1fr; } }
.fld label { display: block; font-size: 11px; font-weight: 600; letter-spacing: .06em; text-transform: uppercase; color: var(--ink-3); margin-bottom: 6px; }
.fld .req { color: var(--bad); margin-left: 2px; }
.fld select, .fld input, .fld .currency-in {
  width: 100%; height: 40px; border: 1px solid var(--line); border-radius: 10px;
  padding: 0 12px; font-size: 13.5px; font-weight: 500; color: var(--ink);
  background: #fff; outline: none; font-family: 'Inter', inherit;
}
.fld select:focus, .fld input:focus { border-color: var(--brand); box-shadow: 0 0 0 3px rgba(19,42,86,.1); }
.fld .currency-in { display: flex; align-items: center; padding: 0 0 0 12px; }
.fld .currency-in span { color: var(--ink-3); font-weight: 600; margin-right: 4px; }
.fld .currency-in input { border: 0; height: 38px; padding: 0; background: transparent; font-family: 'JetBrains Mono', monospace; }

/* toggle ingreso/egreso */
.toggle-tipo { display: flex; background: #f1f5f9; border-radius: 10px; padding: 3px; height: 40px; }
.toggle-tipo button { flex: 1; border: 0; background: transparent; border-radius: 7px; font-weight: 600; font-size: 12.5px; color: var(--ink-3); display: flex; align-items: center; justify-content: center; gap: 6px; cursor: pointer; transition: .12s; }
.toggle-tipo button.on.ing { background: #fff; color: var(--ok); box-shadow: 0 1px 3px rgba(0,0,0,.06); }
.toggle-tipo button.on.egr { background: #fff; color: var(--bad); box-shadow: 0 1px 3px rgba(0,0,0,.06); }

/* fc-specific btn overrides (won't conflict with AdminLTE) */
.btn-fc-brand { background: var(--brand); color: #fff !important; border: 0; height: 40px; padding: 0 18px; border-radius: 10px; font-weight: 600; font-size: 13px; display: inline-flex; align-items: center; gap: 8px; cursor: pointer; }
.btn-fc-brand:hover { background: var(--brand-2); color: #fff !important; }
.btn-fc-outline { background: transparent; color: var(--brand) !important; border: 1px solid var(--brand); height: 40px; padding: 0 14px; border-radius: 10px; font-weight: 600; font-size: 13px; display: inline-flex; align-items: center; gap: 8px; cursor: pointer; }
.btn-fc-outline:hover { background: var(--brand); color: #fff !important; }

/* ─── Movement tabs ─────────────────────────────────────────────── */
.mv-tabs { display: flex; gap: 2px; padding: 0 18px; border-bottom: 1px solid var(--line-2); background: #fafbfd; }
.mv-tab { padding: 12px 14px; border: 0; background: transparent; font-size: 13px; font-weight: 600; color: var(--ink-3); position: relative; display: flex; align-items: center; gap: 8px; border-bottom: 2px solid transparent; margin-bottom: -1px; cursor: pointer; }
.mv-tab .badge-count { font-size: 10.5px; font-weight: 700; background: #e2e8f0; color: var(--ink-3); padding: 2px 7px; border-radius: 999px; }
.mv-tab.active { color: var(--brand); border-bottom-color: var(--brand); }
.mv-tab.active .badge-count { background: var(--brand-soft); color: var(--brand); }
.mv-tab .dt { width: 6px; height: 6px; border-radius: 50%; }
.mv-tab.cc .dt { background: var(--ok); }
.mv-tab.tecno .dt { background: var(--info); }
.mv-tab.tb .dt { background: var(--warn); }

/* ─── Tables ─────────────────────────────────────────────────────── */
.tbl { width: 100%; border-collapse: collapse; font-size: 13px; }
.tbl thead th { background: #fafbfd; color: var(--ink-3); font-size: 10.5px; font-weight: 700; letter-spacing: .08em; text-transform: uppercase; padding: 10px 14px; border-bottom: 1px solid var(--line-2); text-align: left; }
.tbl thead th.r { text-align: right; }
.tbl tbody td { padding: 11px 14px; border-bottom: 1px solid var(--line-2); vertical-align: middle; }
.tbl tbody tr:hover { background: #fafbfd; }
.tbl tbody tr:last-child td { border-bottom: 0; }
.tbl tfoot td { padding: 11px 14px; background: #fafbfd; font-size: 13px; font-weight: 700; border-top: 1px solid var(--line); }
.tbl .r { text-align: right; }
.tbl .hora { color: var(--ink-3); font-weight: 600; font-size: 12.5px; font-family: 'JetBrains Mono', monospace; }
.tbl .monto { font-family: 'JetBrains Mono', monospace; font-weight: 700; font-size: 13.5px; }
.tbl .monto.up { color: var(--ok); }
.tbl .monto.down { color: var(--bad); }

.tipo-pill { display: inline-flex; align-items: center; gap: 6px; font-size: 11px; font-weight: 600; padding: 3px 9px; border-radius: 999px; }
.tipo-pill.ing { background: var(--ok-soft); color: var(--ok); }
.tipo-pill.egr { background: var(--bad-soft); color: var(--bad); }
.tipo-pill.neu { background: #f1f5f9; color: var(--ink-3); }

.medio-tag { display: inline-flex; align-items: center; gap: 6px; font-size: 11.5px; color: var(--ink-2); font-weight: 500; }
.medio-tag .md { width: 8px; height: 8px; border-radius: 2px; }
.medio-tag .md.ef { background: var(--ok); }
.medio-tag .md.cd { background: var(--violet); }
.medio-tag .md.tr { background: var(--info); }
.medio-tag .md.dp { background: var(--ink-3); }
.medio-tag .md.tb { background: var(--warn); }
.medio-tag .md.dv { background: #0bb5b5; }
.medio-tag .md.oth { background: var(--ink-4); }

.estado-tag { display: inline-flex; align-items: center; gap: 5px; font-size: 11px; font-weight: 600; padding: 3px 8px; border-radius: 6px; }
.estado-tag.activo { background: #eef9f3; color: var(--ok); }
.estado-tag.anulado { background: #f1f5f9; color: var(--ink-3); text-decoration: line-through; }

.row-act { width: 30px; height: 30px; border: 0; border-radius: 8px; background: transparent; color: var(--ink-3); cursor: pointer; display: inline-flex; align-items: center; justify-content: center; }
.row-act:hover { background: var(--bad-soft); color: var(--bad); }

.empty-state { padding: 36px 20px; text-align: center; color: var(--ink-3); }
.empty-state .em-ic { width: 48px; height: 48px; border-radius: 14px; background: #f1f5f9; display: inline-flex; align-items: center; justify-content: center; font-size: 18px; color: var(--ink-4); margin-bottom: 8px; }
.empty-state .em-t { font-weight: 600; color: var(--ink-2); font-size: 13.5px; }

/* ─── Breakdown table ───────────────────────────────────────────── */
.breakdown { width: 100%; font-size: 12.5px; border-collapse: collapse; }
.breakdown th { background: #fafbfd; color: var(--ink-3); font-size: 10px; font-weight: 700; letter-spacing: .08em; text-transform: uppercase; padding: 8px 16px; text-align: left; border-bottom: 1px solid var(--line-2); }
.breakdown th.r { text-align: right; }
.breakdown td { padding: 9px 16px; border-bottom: 1px solid var(--line-2); color: var(--ink-2); }
.breakdown td.r { text-align: right; font-family: 'JetBrains Mono', monospace; font-weight: 600; }
.breakdown td.up { color: var(--ok); }
.breakdown td.down { color: var(--bad); }
.breakdown tr:last-child td { border-bottom: 0; }
.breakdown tfoot td { background: #fafbfd; font-weight: 700; color: var(--ink); border-top: 1px solid var(--line); }

/* ─── Sidebar summary cards ─────────────────────────────────────── */
.res-card { background: #fff; border: 1px solid var(--line); border-radius: 14px; margin-bottom: 14px; overflow: hidden; }
.res-head { display: flex; align-items: center; justify-content: space-between; padding: 14px 16px; }
.res-head .lbl { display: flex; align-items: center; gap: 8px; font-size: 11.5px; font-weight: 700; letter-spacing: .08em; text-transform: uppercase; color: var(--ink-3); }
.res-head .lbl .ic { width: 24px; height: 24px; border-radius: 7px; display: flex; align-items: center; justify-content: center; font-size: 11px; }
.res-card.r-cc    .lbl .ic { background: var(--ok-soft);   color: var(--ok); }
.res-card.r-tecno .lbl .ic { background: var(--info-soft); color: var(--info); }
.res-card.r-tb    .lbl .ic { background: var(--warn-soft); color: var(--warn); }

.res-rows { padding: 4px 16px 12px; }
.res-row { display: flex; align-items: center; justify-content: space-between; padding: 7px 0; font-size: 13px; border-bottom: 1px solid var(--line-2); }
.res-row:last-child { border-bottom: 0; }
.res-row .l { color: var(--ink-3); }
.res-row .l small { color: var(--ink-4); font-size: 11px; }
.res-row .v { font-family: 'JetBrains Mono', monospace; font-weight: 600; color: var(--ink); }
.res-row .v.up   { color: var(--ok); }
.res-row .v.down { color: var(--bad); }

.res-total { margin: 6px 16px 16px; padding: 14px; border-radius: 10px; background: linear-gradient(135deg, var(--brand) 0%, var(--brand-2) 100%); color: #fff; display: flex; align-items: center; justify-content: space-between; }
.res-total .lbl { font-size: 11.5px; font-weight: 700; letter-spacing: .08em; text-transform: uppercase; opacity: .78; }
.res-total .val { font-family: 'JetBrains Mono', monospace; font-weight: 700; font-size: 22px; letter-spacing: -0.02em; }
.res-card.r-tecno .res-total { background: linear-gradient(135deg, #0b6cc4 0%, #1080d8 100%); }
.res-card.r-tb    .res-total { background: linear-gradient(135deg, #c47f0f 0%, #e09a1f 100%); }

.chip-ok { font-size: 10.5px; font-weight: 700; padding: 3px 9px; border-radius: 999px; background: var(--ok-soft); color: var(--ok); }

/* ─── Sticky bottom bar ─────────────────────────────────────────── */
.fc-bottom-bar {
  position: fixed; left: 250px; right: 0; bottom: 0;
  background: rgba(255,255,255,.92);
  backdrop-filter: saturate(140%) blur(8px);
  -webkit-backdrop-filter: saturate(140%) blur(8px);
  border-top: 1px solid var(--line);
  padding: 12px 22px;
  display: flex; align-items: center; justify-content: space-between; gap: 14px;
  z-index: 30;
  transition: left .3s;
}
.sidebar-collapse .fc-bottom-bar { left: 4.6rem; }
@media(max-width:768px){ .fc-bottom-bar { left: 0; } }
.fc-bottom-bar .bb-summary { display: flex; align-items: center; gap: 18px; font-size: 12.5px; color: var(--ink-3); flex-wrap: wrap; }
.fc-bottom-bar .bb-item { display: flex; align-items: center; gap: 7px; }
.fc-bottom-bar .bb-item b { color: var(--ink); font-family: 'JetBrains Mono', monospace; font-weight: 700; }
.fc-bottom-bar .bb-sep { width: 1px; height: 18px; background: var(--line); }
.btn-cerrar-caja {
  background: var(--brand); color: #fff; border: 0;
  height: 42px; padding: 0 22px; border-radius: 10px;
  font-weight: 700; font-size: 13.5px;
  display: inline-flex; align-items: center; gap: 10px;
  box-shadow: 0 6px 18px rgba(19,42,86,.25); cursor: pointer; white-space: nowrap;
}
.btn-cerrar-caja:hover { background: var(--brand-2); color: #fff; }
.btn-reabrir-caja {
  background: transparent; color: var(--brand); border: 1px solid var(--brand);
  height: 42px; padding: 0 22px; border-radius: 10px;
  font-weight: 700; font-size: 13.5px;
  display: inline-flex; align-items: center; gap: 10px; cursor: pointer; white-space: nowrap;
}
.btn-reabrir-caja:hover { background: var(--brand); color: #fff; }

/* search box inside card tools */
.mv-search { height: 32px; border: 1px solid var(--line); border-radius: 8px; padding: 0 12px 0 32px; font-size: 12.5px; background: url("data:image/svg+xml;utf8,<svg xmlns='http://www.w3.org/2000/svg' width='14' height='14' viewBox='0 0 24 24' fill='none' stroke='%2394a3b8' stroke-width='2.5' stroke-linecap='round' stroke-linejoin='round'><circle cx='11' cy='11' r='8'/><path d='m21 21-4.3-4.3'/></svg>") no-repeat 10px center; width: 200px; outline: none; font-family: 'Inter', inherit; }
.mv-search:focus { border-color: var(--brand); }
</style>
@endpush

@section('content')

{{-- ─── HIDDEN VARS for JS ──────────────────────────────────────── --}}
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

<div class="page-shell" id="flujo-caja-app">
@include('flash-message')

{{-- ─── PAGE HEADER ───────────────────────────────────────────────── --}}
<div class="page-head">
    <div class="page-title">
        <div class="ico"><i class="fas fa-cash-register"></i></div>
        <div>
            <div class="crumb">Administración <i class="fas fa-chevron-right"></i> <span>Flujo de Caja</span></div>
            <h1>Flujo de Caja</h1>
        </div>
    </div>
    <div class="head-actions">
        {{-- estado pill --}}
        @if($caja->isAbierta())
        <span class="estado-pill" id="badge-estado">
            <span class="dot"></span>
            Caja abierta · <span id="fecha-display">{{ $fecha->locale('es')->isoFormat('dddd D MMM') }}</span>
        </span>
        @else
        <span class="estado-pill cerrada" id="badge-estado">
            <span class="dot"></span>
            Caja cerrada · <span id="fecha-display">{{ $fecha->locale('es')->isoFormat('dddd D MMM') }}</span>
        </span>
        @endif

        {{-- date picker --}}
        <div class="date-pick">
            <button class="nav-day" id="btn-prev-day" title="Día anterior"><i class="fas fa-chevron-left"></i></button>
            <i class="fas fa-calendar-alt ic"></i>
            <input type="date" id="selector-fecha"
                   value="{{ $fecha->toDateString() }}"
                   max="{{ \Carbon\Carbon::today()->toDateString() }}">
            <button class="nav-day" id="btn-next-day" title="Día siguiente"
                {{ $fecha->isToday() ? 'disabled' : '' }}><i class="fas fa-chevron-right"></i></button>
        </div>

        <a href="{{ route('flujo_caja.logs.index') }}" class="btn-ghost">
            <i class="fas fa-history"></i> Historial
        </a>
        <button class="btn-ghost" title="Exportar" onclick="window.print()">
            <i class="fas fa-file-export"></i>
        </button>
    </div>
</div>

{{-- ─── KPI ROW ────────────────────────────────────────────────────── --}}
<div class="kpi-row">
    {{-- Apertura Caja Chica --}}
    <div class="kpi k-apertura">
        <div class="accent"></div>
        <div class="kpi-label"><span class="ic"><i class="fas fa-coins"></i></span>Apertura Caja Chica</div>
        <div class="kpi-value num" id="kpi-apertura-caja-display">${{ number_format($caja->apertura_caja, 0, ',', '.') }}</div>
        <div class="kpi-editor">
            <div class="currency">
                <span>$</span>
                <input type="number" id="apertura-caja"
                       value="{{ number_format($caja->apertura_caja, 0, '.', '') }}"
                       step="1" min="0" {{ !$caja->isAbierta() ? 'disabled' : '' }}>
            </div>
            <button class="save-btn btn-guardar-apertura" data-campo="apertura_caja"
                    title="Guardar" {{ !$caja->isAbierta() ? 'disabled' : '' }}>
                <i class="fas fa-check"></i>
            </button>
        </div>
    </div>

    {{-- Apertura Tecnoelectro --}}
    <div class="kpi k-tecno">
        <div class="accent"></div>
        <div class="kpi-label"><span class="ic"><i class="fas fa-file-invoice-dollar"></i></span>Apertura Tecnoelectro</div>
        <div class="kpi-value num" id="kpi-apertura-tecno-display">${{ number_format($caja->apertura_tecnoelectro, 0, ',', '.') }}</div>
        <div class="kpi-editor">
            <div class="currency">
                <span>$</span>
                <input type="number" id="apertura-tecnoelectro"
                       value="{{ number_format($caja->apertura_tecnoelectro, 0, '.', '') }}"
                       step="1" min="0" {{ !$caja->isAbierta() ? 'disabled' : '' }}>
            </div>
            <button class="save-btn btn-guardar-apertura" data-campo="apertura_tecnoelectro"
                    title="Guardar" {{ !$caja->isAbierta() ? 'disabled' : '' }}>
                <i class="fas fa-check"></i>
            </button>
        </div>
    </div>

    {{-- Total Ingresos --}}
    <div class="kpi k-ing">
        <div class="accent"></div>
        <div class="kpi-label"><span class="ic"><i class="fas fa-arrow-up"></i></span>Total Ingresos del Día</div>
        <div class="kpi-value num" id="kpi-total-ing">+${{ number_format($totales['total_ingresos'] + $totales['total_ingresos_tecno'] + $totales['ingreso_transbank'], 0, ',', '.') }}</div>
        <div class="kpi-sub" id="res-total-ing" style="display:none">${{ number_format($totales['total_ingresos'], 0, ',', '.') }}</div>
    </div>

    {{-- Total Egresos --}}
    <div class="kpi k-egr">
        <div class="accent"></div>
        <div class="kpi-label"><span class="ic"><i class="fas fa-arrow-down"></i></span>Total Egresos del Día</div>
        <div class="kpi-value num" id="kpi-total-egr">−${{ number_format($totales['total_egresos'] + $totales['total_egresos_tecno'] + $totales['egreso_transbank'], 0, ',', '.') }}</div>
        <div class="kpi-sub" id="res-total-egr" style="display:none">-${{ number_format($totales['total_egresos'], 0, ',', '.') }}</div>
    </div>
</div>

{{-- ─── MAIN GRID ──────────────────────────────────────────────────── --}}
<div class="main-grid">

    {{-- LEFT COLUMN ─────────────────────────────────────────────── --}}
    <div>

        {{-- FORM: Registrar movimiento --}}
        @if($caja->isAbierta())
        <div class="vcard" id="card-nuevo-movimiento">
            <div class="vcard-head">
                <div class="vcard-title t-form">
                    <span class="tic"><i class="fas fa-plus"></i></span>
                    Registrar movimiento
                    <small>· se asigna automáticamente al medio elegido</small>
                </div>
                <div class="vcard-tools">
                    <span class="chip">Caja #{{ $caja->id }}</span>
                    <button class="ico-btn" data-card-widget="collapse" title="Colapsar">
                        <i class="fas fa-chevron-up"></i>
                    </button>
                </div>
            </div>
            <div class="vcard-body">
                <form id="form-movimiento">
                    <input type="hidden" name="caja_id" value="{{ $caja->id }}">
                    <input type="hidden" name="tipo_movimiento" id="hidden-tipo" value="ingreso" required>
                    <div class="mv-form">
                        {{-- Tipo toggle --}}
                        <div class="fld">
                            <label>Tipo <span class="req">*</span></label>
                            <div class="toggle-tipo" id="toggle-tipo">
                                <button type="button" class="on ing" data-v="ingreso">
                                    <i class="fas fa-arrow-up"></i> Ingreso
                                </button>
                                <button type="button" class="egr" data-v="egreso">
                                    <i class="fas fa-arrow-down"></i> Egreso
                                </button>
                            </div>
                        </div>

                        {{-- Medio --}}
                        <div class="fld">
                            <label>Medio <span class="req">*</span></label>
                            <select name="medio" required>
                                <option value="">— Seleccionar medio —</option>
                                <optgroup label="Caja Chica">
                                    <option value="efectivo">Efectivo</option>
                                    <option value="credito_debito">Crédito / Débito</option>
                                    <option value="transferencia">Transferencia</option>
                                    <option value="deposito_banco">Depósito Banco</option>
                                </optgroup>
                                <optgroup label="Tecnoelectro">
                                    <option value="efectivo_tecno">Efectivo Tecnoelectro</option>
                                    <option value="credito_debito_tecno">Créd./Déb. Tecnoelectro</option>
                                    <option value="devolucion_abono">Devolución Abono</option>
                                    <option value="deposito_banco_tecnoelectro">Depósito Banco Tecnoelectro</option>
                                </optgroup>
                                <optgroup label="Transbank">
                                    <option value="transbank">Transbank</option>
                                </optgroup>
                            </select>
                        </div>

                        {{-- Monto --}}
                        <div class="fld">
                            <label>Monto <span class="req">*</span></label>
                            <div class="currency-in">
                                <span>$</span>
                                <input type="number" name="monto" step="1" required placeholder="0">
                            </div>
                        </div>

                        {{-- Detalle --}}
                        <div class="fld">
                            <label>Detalle <span class="req">*</span></label>
                            <input type="text" name="detalle" required maxlength="255" placeholder="Descripción breve del movimiento">
                        </div>

                        {{-- Acciones --}}
                        <div class="fld" style="display:flex;gap:8px;">
                            <button type="submit" class="btn-fc-brand"><i class="fas fa-plus"></i> Registrar</button>
                            <button type="reset" class="btn-fc-outline" title="Limpiar"><i class="fas fa-undo"></i></button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
        @endif

        {{-- MOVIMIENTOS: card con pestañas --}}
        <div class="vcard">
            <div class="vcard-head no-border">
                <div class="vcard-title t-list">
                    <span class="tic"><i class="fas fa-list-ul"></i></span>
                    Movimientos del día
                </div>
                <div class="vcard-tools">
                    <input type="search" class="mv-search" id="mv-search" placeholder="Buscar detalle…">
                </div>
            </div>

            {{-- Tabs --}}
            <div class="mv-tabs" id="mv-tabs">
                @php
                    $movsCajaChica  = $movimientos->filter(fn($m) => !$m->esTecnoelectro() && !$m->esTransbank());
                    $movsTecno      = $movimientos->filter(fn($m) => $m->esTecnoelectro());
                    $movsTransbank  = $movimientos->filter(fn($m) => $m->esTransbank());
                @endphp
                <button class="mv-tab cc active" data-tab="cc">
                    <span class="dt"></span> Caja Chica
                    <span class="badge-count">{{ $movsCajaChica->count() }}</span>
                </button>
                <button class="mv-tab tecno" data-tab="tecno">
                    <span class="dt"></span> Tecnoelectro
                    <span class="badge-count">{{ $movsTecno->count() }}</span>
                </button>
                <button class="mv-tab tb" data-tab="tb">
                    <span class="dt"></span> Transbank
                    <span class="badge-count">{{ $movsTransbank->count() }}</span>
                </button>
            </div>

            <div class="vcard-body flush">

                {{-- PANE: Caja Chica --}}
                <div class="mv-pane" data-pane="cc">
                    <table class="tbl" id="tabla-movimientos">
                        <thead><tr>
                            <th style="width:65px">Hora</th>
                            <th style="width:100px">Tipo</th>
                            <th>Medio</th>
                            <th>Detalle</th>
                            <th class="r" style="width:130px">Monto</th>
                            <th>Usuario</th>
                            <th style="width:80px">Estado</th>
                            @if($caja->isAbierta())<th style="width:50px"></th>@endif
                        </tr></thead>
                        <tbody id="tbody-movimientos">
                        @forelse($movsCajaChica as $mov)
                            <tr id="fila-{{ $mov->id }}" class="{{ $mov->anulado ? 'text-muted' : '' }}">
                                <td class="hora">{{ $mov->created_at->timezone(config('app.timezone'))->format('H:i') }}</td>
                                <td>
                                    @if($mov->anulado)
                                        <span class="tipo-pill neu"><i class="fas fa-minus"></i> {{ $mov->getTipoLabel() }}</span>
                                    @elseif($mov->tipo_movimiento === 'ingreso')
                                        <span class="tipo-pill ing"><i class="fas fa-arrow-up"></i> Ingreso</span>
                                    @else
                                        <span class="tipo-pill egr"><i class="fas fa-arrow-down"></i> Egreso</span>
                                    @endif
                                </td>
                                <td><span class="medio-tag"><span class="md {{ $mov->medio === 'efectivo' ? 'ef' : ($mov->medio === 'credito_debito' ? 'cd' : ($mov->medio === 'transferencia' ? 'tr' : 'dp')) }}"></span>{{ $mov->getMedioLabel() }}</span></td>
                                <td>{{ $mov->detalle ?? '—' }}</td>
                                <td class="r monto {{ $mov->anulado ? '' : ($mov->tipo_movimiento === 'ingreso' ? 'up' : 'down') }}">
                                    {{ $mov->anulado ? '' : ($mov->tipo_movimiento === 'ingreso' ? '+' : '−') }}${{ number_format($mov->monto, 0, ',', '.') }}
                                </td>
                                <td style="font-size:12.5px;color:var(--ink-3)">{{ $mov->usuario->name ?? '—' }}</td>
                                <td>
                                    @if($mov->anulado)
                                        <span class="estado-tag anulado">Anulado</span>
                                    @else
                                        <span class="estado-tag activo">Activo</span>
                                    @endif
                                </td>
                                @if($caja->isAbierta())
                                <td>
                                    @if(!$mov->anulado)
                                    <button class="row-act btn-anular" data-id="{{ $mov->id }}" title="Anular">
                                        <i class="fas fa-times"></i>
                                    </button>
                                    @endif
                                </td>
                                @endif
                            </tr>
                        @empty
                            <tr id="fila-vacia">
                                <td colspan="{{ $caja->isAbierta() ? 8 : 7 }}">
                                    <div class="empty-state">
                                        <div class="em-ic"><i class="fas fa-coins"></i></div>
                                        <div class="em-t">Sin movimientos de Caja Chica</div>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>

                {{-- PANE: Tecnoelectro --}}
                <div class="mv-pane" data-pane="tecno" style="display:none">
                    <table class="tbl" id="tabla-movimientos-tecno">
                        <thead><tr>
                            <th style="width:65px">Hora</th>
                            <th style="width:100px">Tipo</th>
                            <th>Medio</th>
                            <th>Detalle</th>
                            <th class="r" style="width:130px">Monto</th>
                            <th>Usuario</th>
                            <th style="width:80px">Estado</th>
                            @if($caja->isAbierta())<th style="width:50px"></th>@endif
                        </tr></thead>
                        <tbody id="tbody-movimientos-tecno">
                        @forelse($movsTecno as $mov)
                            <tr id="fila-{{ $mov->id }}" class="{{ $mov->anulado ? 'text-muted' : '' }}">
                                <td class="hora">{{ $mov->created_at->timezone(config('app.timezone'))->format('H:i') }}</td>
                                <td>
                                    @if($mov->anulado)
                                        <span class="tipo-pill neu"><i class="fas fa-minus"></i> {{ $mov->getTipoLabel() }}</span>
                                    @elseif($mov->tipo_movimiento === 'ingreso')
                                        <span class="tipo-pill ing"><i class="fas fa-arrow-up"></i> Ingreso</span>
                                    @else
                                        <span class="tipo-pill egr"><i class="fas fa-arrow-down"></i> Egreso</span>
                                    @endif
                                </td>
                                <td><span class="medio-tag"><span class="md {{ in_array($mov->medio, ['efectivo_tecno']) ? 'ef' : (in_array($mov->medio, ['credito_debito_tecno']) ? 'cd' : (in_array($mov->medio, ['devolucion_abono']) ? 'dv' : 'dp')) }}"></span>{{ $mov->getMedioLabel() }}</span></td>
                                <td>{{ $mov->detalle ?? '—' }}</td>
                                <td class="r monto {{ $mov->anulado ? '' : ($mov->tipo_movimiento === 'ingreso' ? 'up' : 'down') }}">
                                    {{ $mov->anulado ? '' : ($mov->tipo_movimiento === 'ingreso' ? '+' : '−') }}${{ number_format($mov->monto, 0, ',', '.') }}
                                </td>
                                <td style="font-size:12.5px;color:var(--ink-3)">{{ $mov->usuario->name ?? '—' }}</td>
                                <td>
                                    @if($mov->anulado)
                                        <span class="estado-tag anulado">Anulado</span>
                                    @else
                                        <span class="estado-tag activo">Activo</span>
                                    @endif
                                </td>
                                @if($caja->isAbierta())
                                <td>
                                    @if(!$mov->anulado)
                                    <button class="row-act btn-anular" data-id="{{ $mov->id }}" title="Anular">
                                        <i class="fas fa-times"></i>
                                    </button>
                                    @endif
                                </td>
                                @endif
                            </tr>
                        @empty
                            <tr id="fila-vacia-tecno">
                                <td colspan="{{ $caja->isAbierta() ? 8 : 7 }}">
                                    <div class="empty-state">
                                        <div class="em-ic"><i class="fas fa-file-invoice-dollar"></i></div>
                                        <div class="em-t">Sin movimientos Tecnoelectro</div>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>

                {{-- PANE: Transbank --}}
                <div class="mv-pane" data-pane="tb" style="display:none">
                    <table class="tbl" id="tabla-movimientos-transbank">
                        <thead><tr>
                            <th style="width:65px">Hora</th>
                            <th style="width:100px">Tipo</th>
                            <th>Medio</th>
                            <th>Detalle</th>
                            <th class="r" style="width:130px">Monto</th>
                            <th>Usuario</th>
                            <th style="width:80px">Estado</th>
                            @if($caja->isAbierta())<th style="width:50px"></th>@endif
                        </tr></thead>
                        <tbody id="tbody-movimientos-transbank">
                        @forelse($movsTransbank as $mov)
                            <tr id="fila-{{ $mov->id }}" class="{{ $mov->anulado ? 'text-muted' : '' }}">
                                <td class="hora">{{ $mov->created_at->timezone(config('app.timezone'))->format('H:i') }}</td>
                                <td>
                                    @if($mov->anulado)
                                        <span class="tipo-pill neu"><i class="fas fa-minus"></i> {{ $mov->getTipoLabel() }}</span>
                                    @elseif($mov->tipo_movimiento === 'ingreso')
                                        <span class="tipo-pill ing"><i class="fas fa-arrow-up"></i> Ingreso</span>
                                    @else
                                        <span class="tipo-pill egr"><i class="fas fa-arrow-down"></i> Egreso</span>
                                    @endif
                                </td>
                                <td><span class="medio-tag"><span class="md tb"></span>{{ $mov->getMedioLabel() }}</span></td>
                                <td>{{ $mov->detalle ?? '—' }}</td>
                                <td class="r monto {{ $mov->anulado ? '' : ($mov->tipo_movimiento === 'ingreso' ? 'up' : 'down') }}">
                                    {{ $mov->anulado ? '' : ($mov->tipo_movimiento === 'ingreso' ? '+' : '−') }}${{ number_format($mov->monto, 0, ',', '.') }}
                                </td>
                                <td style="font-size:12.5px;color:var(--ink-3)">{{ $mov->usuario->name ?? '—' }}</td>
                                <td>
                                    @if($mov->anulado)
                                        <span class="estado-tag anulado">Anulado</span>
                                    @else
                                        <span class="estado-tag activo">Activo</span>
                                    @endif
                                </td>
                                @if($caja->isAbierta())
                                <td>
                                    @if(!$mov->anulado)
                                    <button class="row-act btn-anular" data-id="{{ $mov->id }}" title="Anular">
                                        <i class="fas fa-times"></i>
                                    </button>
                                    @endif
                                </td>
                                @endif
                            </tr>
                        @empty
                            <tr id="fila-vacia-transbank">
                                <td colspan="{{ $caja->isAbierta() ? 8 : 7 }}">
                                    <div class="empty-state">
                                        <div class="em-ic"><i class="fas fa-credit-card"></i></div>
                                        <div class="em-t">Sin movimientos Transbank</div>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                        </tbody>
                        <tfoot>
                            <tr>
                                <td colspan="{{ $caja->isAbierta() ? 4 : 3 }}" style="color:var(--ink-3);font-weight:600;font-size:12px;">
                                    {{ $movsTransbank->count() }} movimiento(s) · Transbank
                                </td>
                                <td class="r num" id="transbank-total-monto">
                                    ${{ number_format($totales['ingreso_transbank'] - $totales['egreso_transbank'], 0, ',', '.') }}
                                </td>
                                <td colspan="{{ $caja->isAbierta() ? 3 : 2 }}"></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>

            </div>{{-- /vcard-body --}}
        </div>{{-- /vcard movimientos --}}

        {{-- ─── RESUMEN POR MEDIO: Caja Chica ─────────────────────── --}}
        <div class="vcard" id="seccion-resumen">
            <div class="vcard-head">
                <div class="vcard-title t-resumen">
                    <span class="tic"><i class="fas fa-th-list"></i></span>
                    Resumen por medio
                </div>
                <div class="vcard-tools"><span class="chip muted">Caja Chica</span></div>
            </div>
            <table class="breakdown">
                <thead><tr>
                    <th>Medio</th>
                    <th class="r">Ingresos</th>
                    <th class="r">Egresos</th>
                    <th class="r">Neto</th>
                </tr></thead>
                <tbody>
                    <tr>
                        <td><span class="medio-tag"><span class="md ef"></span> Efectivo</span></td>
                        <td class="r up" id="res-ing-efectivo">+${{ number_format($totales['ingreso_efectivo'], 0, ',', '.') }}</td>
                        <td class="r down" id="res-egr-efectivo">-${{ number_format($totales['egreso_efectivo'], 0, ',', '.') }}</td>
                        <td class="r" id="res-net-efectivo"><b>${{ number_format($totales['ingreso_efectivo'] - $totales['egreso_efectivo'], 0, ',', '.') }}</b></td>
                    </tr>
                    <tr>
                        <td><span class="medio-tag"><span class="md cd"></span> Crédito/Débito</span></td>
                        <td class="r up" id="res-ing-credito">+${{ number_format($totales['ingreso_credito_debito'], 0, ',', '.') }}</td>
                        <td class="r down" id="res-egr-credito">-${{ number_format($totales['egreso_credito_debito'], 0, ',', '.') }}</td>
                        <td class="r" id="res-net-credito"><b>${{ number_format($totales['ingreso_credito_debito'] - $totales['egreso_credito_debito'], 0, ',', '.') }}</b></td>
                    </tr>
                    <tr>
                        <td><span class="medio-tag"><span class="md tr"></span> Transferencia</span></td>
                        <td class="r up" id="res-ing-trans">+${{ number_format($totales['ingreso_transferencia'], 0, ',', '.') }}</td>
                        <td class="r down" id="res-egr-trans">-${{ number_format($totales['egreso_transferencia'], 0, ',', '.') }}</td>
                        <td class="r" id="res-net-trans"><b>${{ number_format($totales['ingreso_transferencia'] - $totales['egreso_transferencia'], 0, ',', '.') }}</b></td>
                    </tr>
                    <tr>
                        <td><span class="medio-tag"><span class="md dp"></span> Depósito Banco</span></td>
                        <td class="r down" colspan="2" id="res-deposito-banco">-${{ number_format($totales['deposito_banco'], 0, ',', '.') }}</td>
                        <td></td>
                    </tr>
                </tbody>
                <tfoot>
                    <tr>
                        <td>TOTALES</td>
                        <td class="r up" id="res-total-ing">+${{ number_format($totales['total_ingresos'], 0, ',', '.') }}</td>
                        <td class="r down" id="res-total-egr">-${{ number_format($totales['total_egresos'], 0, ',', '.') }}</td>
                        <td class="r" id="res-total-net"><b>${{ number_format($totales['total_ingresos'] - $totales['total_egresos'], 0, ',', '.') }}</b></td>
                    </tr>
                </tfoot>
            </table>
        </div>

        {{-- ─── RESUMEN POR MEDIO: Tecnoelectro ────────────────────── --}}
        <div class="vcard">
            <div class="vcard-head">
                <div class="vcard-title t-resumen">
                    <span class="tic"><i class="fas fa-th-list"></i></span>
                    Resumen por medio
                </div>
                <div class="vcard-tools"><span class="chip muted">Tecnoelectro</span></div>
            </div>
            <table class="breakdown">
                <thead><tr>
                    <th>Medio</th>
                    <th class="r">Ingresos</th>
                    <th class="r">Egresos</th>
                    <th class="r">Neto</th>
                </tr></thead>
                <tbody>
                    <tr>
                        <td><span class="medio-tag"><span class="md ef"></span> Efectivo Tecnoelectro</span></td>
                        <td class="r up" id="res-ing-efectivo-tecno">+${{ number_format($totales['ingreso_efectivo_tecno'], 0, ',', '.') }}</td>
                        <td class="r down" id="res-egr-efectivo-tecno">-${{ number_format($totales['egreso_efectivo_tecno'], 0, ',', '.') }}</td>
                        <td class="r" id="res-net-efectivo-tecno"><b>${{ number_format($totales['ingreso_efectivo_tecno'] - $totales['egreso_efectivo_tecno'], 0, ',', '.') }}</b></td>
                    </tr>
                    <tr>
                        <td><span class="medio-tag"><span class="md cd"></span> Créd./Déb. Tecnoelectro</span></td>
                        <td class="r up" id="res-ing-credito-tecno">+${{ number_format($totales['ingreso_credito_debito_tecno'], 0, ',', '.') }}</td>
                        <td class="r down" id="res-egr-credito-tecno">-${{ number_format($totales['egreso_credito_debito_tecno'], 0, ',', '.') }}</td>
                        <td class="r" id="res-net-credito-tecno"><b>${{ number_format($totales['ingreso_credito_debito_tecno'] - $totales['egreso_credito_debito_tecno'], 0, ',', '.') }}</b></td>
                    </tr>
                    <tr>
                        <td><span class="medio-tag"><span class="md dv"></span> Devolución Abono</span></td>
                        <td class="r up" id="res-ing-devolucion-abono">+${{ number_format($totales['ingreso_devolucion_abono'], 0, ',', '.') }}</td>
                        <td class="r down" id="res-egr-devolucion-abono">-${{ number_format($totales['egreso_devolucion_abono'], 0, ',', '.') }}</td>
                        <td class="r" id="res-net-devolucion-abono"><b>${{ number_format($totales['ingreso_devolucion_abono'] - $totales['egreso_devolucion_abono'], 0, ',', '.') }}</b></td>
                    </tr>
                    <tr>
                        <td><span class="medio-tag"><span class="md dp"></span> Depósito Banco Tecnoelectro</span></td>
                        <td class="r down" colspan="2" id="res-deposito-banco-tecno">-${{ number_format($totales['deposito_banco_tecnoelectro'], 0, ',', '.') }}</td>
                        <td></td>
                    </tr>
                </tbody>
                <tfoot>
                    <tr>
                        <td>TOTALES</td>
                        <td class="r up" id="res-total-ing-tecno">+${{ number_format($totales['total_ingresos_tecno'], 0, ',', '.') }}</td>
                        <td class="r down" id="res-total-egr-tecno">-${{ number_format($totales['total_egresos_tecno'], 0, ',', '.') }}</td>
                        <td class="r" id="res-total-net-tecno"><b>${{ number_format($totales['total_ingresos_tecno'] - $totales['total_egresos_tecno'], 0, ',', '.') }}</b></td>
                    </tr>
                </tfoot>
            </table>
        </div>

    </div>{{-- /left column --}}

    {{-- RIGHT COLUMN: Cierres ───────────────────────────────────── --}}
    <div id="col-cierres">

        {{-- Cierre Caja Chica --}}
        <div class="res-card r-cc">
            <div class="res-head">
                <span class="lbl"><span class="ic"><i class="fas fa-coins"></i></span> Cierre Caja Chica</span>
                @if($caja->isAbierta())<span class="chip-ok">en curso</span>@endif
            </div>
            <div class="res-rows">
                <div class="res-row">
                    <span class="l">Apertura</span>
                    <span class="v num" id="cierre-apertura-caja">${{ number_format($totales['apertura_caja'], 0, ',', '.') }}</span>
                </div>
                <div class="res-row">
                    <span class="l">+ Ingresos (todos los medios)</span>
                    <span class="v up num" id="cierre-ing-caja">+${{ number_format($totales['total_ingresos'], 0, ',', '.') }}</span>
                </div>
                <div class="res-row">
                    <span class="l">− Egresos (todos los medios)</span>
                    <span class="v down num" id="cierre-egr-caja">-${{ number_format($totales['total_egresos'] - $totales['deposito_banco'], 0, ',', '.') }}</span>
                </div>
                <div class="res-row">
                    <span class="l">− Depósito banco</span>
                    <span class="v down num" id="cierre-deposito">-${{ number_format($totales['deposito_banco'], 0, ',', '.') }}</span>
                </div>
                <div class="res-row">
                    <span class="l">− Neto Créd./Déb. <small>(no físico)</small></span>
                    <span class="v down num" id="cierre-neto-credito">-${{ number_format($totales['neto_credito_debito'], 0, ',', '.') }}</span>
                </div>
                <div class="res-row">
                    <span class="l">− Neto Transferencia <small>(no físico)</small></span>
                    <span class="v down num" id="cierre-neto-transferencia">-${{ number_format($totales['neto_transferencia'], 0, ',', '.') }}</span>
                </div>
            </div>
            <div class="res-total">
                <span class="lbl">= Cierre caja</span>
                <span class="val num" id="cierre-caja-valor">${{ number_format($totales['cierre_caja'], 0, ',', '.') }}</span>
            </div>
        </div>

        {{-- Cierre Tecnoelectro --}}
        <div class="res-card r-tecno">
            <div class="res-head">
                <span class="lbl"><span class="ic"><i class="fas fa-file-invoice-dollar"></i></span> Cierre Tecnoelectro</span>
            </div>
            <div class="res-rows">
                <div class="res-row">
                    <span class="l">Apertura</span>
                    <span class="v num" id="cierre-apertura-tecno">${{ number_format($totales['apertura_tecnoelectro'], 0, ',', '.') }}</span>
                </div>
                <div class="res-row">
                    <span class="l">+ Ingresos Tecnoelectro</span>
                    <span class="v up num" id="cierre-ing-tecno">+${{ number_format($totales['ingreso_tecnoelectro'], 0, ',', '.') }}</span>
                </div>
                <div class="res-row">
                    <span class="l">− Egresos Tecnoelectro</span>
                    <span class="v down num" id="cierre-egr-tecno">-${{ number_format($totales['egreso_tecnoelectro'], 0, ',', '.') }}</span>
                </div>
                <div class="res-row">
                    <span class="l">− Depósito Banco Tecnoelectro</span>
                    <span class="v down num" id="cierre-deposito-tecno">-${{ number_format($totales['deposito_banco_tecnoelectro'], 0, ',', '.') }}</span>
                </div>
                <div class="res-row">
                    <span class="l">− Neto Créd./Déb. Tecno. <small>(no físico)</small></span>
                    <span class="v down num" id="cierre-neto-credito-tecno">-${{ number_format($totales['neto_credito_debito_tecno'], 0, ',', '.') }}</span>
                </div>
            </div>
            <div class="res-total">
                <span class="lbl">= Cierre Tecnoelectro</span>
                <span class="val num" id="cierre-tecno-valor">${{ number_format($totales['cierre_tecnoelectro'], 0, ',', '.') }}</span>
            </div>
        </div>

        {{-- Resumen Transbank --}}
        <div class="res-card r-tb">
            <div class="res-head">
                <span class="lbl"><span class="ic"><i class="fas fa-credit-card"></i></span> Resumen Transbank</span>
            </div>
            <div class="res-rows">
                <div class="res-row">
                    <span class="l">+ Ingresos Transbank</span>
                    <span class="v up num" id="resumen-ing-transbank">+${{ number_format($totales['ingreso_transbank'], 0, ',', '.') }}</span>
                </div>
                <div class="res-row">
                    <span class="l">− Egresos Transbank</span>
                    <span class="v down num" id="resumen-egr-transbank">-${{ number_format($totales['egreso_transbank'], 0, ',', '.') }}</span>
                </div>
            </div>
            <div class="res-total">
                <span class="lbl">= Neto Transbank</span>
                <span class="val num" id="resumen-neto-transbank">${{ number_format($totales['ingreso_transbank'] - $totales['egreso_transbank'], 0, ',', '.') }}</span>
            </div>
        </div>

        {{-- Reabrir caja (caja cerrada) --}}
        @if(!$caja->isAbierta())
        @can('flujo_caja.reabrir')
        <div style="margin-top:6px;text-align:center;">
            <button class="btn-reabrir-caja" id="btn-reabrir-caja" style="width:100%;">
                <i class="fas fa-lock-open"></i> Reabrir caja
            </button>
        </div>
        @endcan
        @endif

    </div>{{-- /col-cierres --}}

</div>{{-- /main-grid --}}

</div>{{-- /page-shell / flujo-caja-app --}}

{{-- ─── STICKY BOTTOM BAR ─────────────────────────────────────────── --}}
<div class="fc-bottom-bar">
    <div class="bb-summary">
        <div class="bb-item">
            <i class="fas fa-coins" style="color:var(--ok)"></i>
            Cierre CC:
            <b class="num" id="bb-cierre-cc">${{ number_format($totales['cierre_caja'], 0, ',', '.') }}</b>
        </div>
        <div class="bb-sep"></div>
        <div class="bb-item">
            <i class="fas fa-file-invoice-dollar" style="color:var(--info)"></i>
            Cierre Tecno:
            <b class="num" id="bb-cierre-tecno">${{ number_format($totales['cierre_tecnoelectro'], 0, ',', '.') }}</b>
        </div>
        <div class="bb-sep"></div>
        <div class="bb-item">
            <i class="fas fa-credit-card" style="color:var(--warn)"></i>
            Neto Transbank:
            <b class="num" id="bb-neto-tb">${{ number_format($totales['ingreso_transbank'] - $totales['egreso_transbank'], 0, ',', '.') }}</b>
        </div>
        <div class="bb-sep"></div>
        <div class="bb-item" style="color:var(--ink-3)">
            <i class="far fa-clock"></i> {{ now()->timezone(config('app.timezone'))->format('H:i') }}
        </div>
    </div>
    @if($caja->isAbierta())
    <button class="btn-cerrar-caja" id="btn-cerrar-caja">
        <i class="fas fa-lock"></i> Cerrar caja del día
    </button>
    @endif
</div>

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
        $('#res-ing-efectivo').text('+' + fmt(t.ingreso_efectivo));
        $('#res-egr-efectivo').text('-' + fmt(t.egreso_efectivo));
        $('#res-net-efectivo').text(fmt(t.ingreso_efectivo - t.egreso_efectivo));

        $('#res-ing-credito').text('+' + fmt(t.ingreso_credito_debito));
        $('#res-egr-credito').text('-' + fmt(t.egreso_credito_debito));
        $('#res-net-credito').text(fmt(t.ingreso_credito_debito - t.egreso_credito_debito));

        $('#res-ing-trans').text('+' + fmt(t.ingreso_transferencia));
        $('#res-egr-trans').text('-' + fmt(t.egreso_transferencia));
        $('#res-net-trans').text(fmt(t.ingreso_transferencia - t.egreso_transferencia));

        $('#res-deposito-banco').text('-' + fmt(t.deposito_banco));
        $('#res-total-ing').text('+' + fmt(t.total_ingresos));
        $('#res-total-egr').text('-' + fmt(t.total_egresos));
        $('#res-total-net').text(fmt(t.total_ingresos - t.total_egresos));

        // Resumen Tecnoelectro
        $('#res-ing-efectivo-tecno').text('+' + fmt(t.ingreso_efectivo_tecno));
        $('#res-egr-efectivo-tecno').text('-' + fmt(t.egreso_efectivo_tecno));
        $('#res-net-efectivo-tecno').text(fmt(t.ingreso_efectivo_tecno - t.egreso_efectivo_tecno));

        $('#res-ing-credito-tecno').text('+' + fmt(t.ingreso_credito_debito_tecno));
        $('#res-egr-credito-tecno').text('-' + fmt(t.egreso_credito_debito_tecno));
        $('#res-net-credito-tecno').text(fmt(t.ingreso_credito_debito_tecno - t.egreso_credito_debito_tecno));

        $('#res-ing-devolucion-abono').text('+' + fmt(t.ingreso_devolucion_abono));
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
        $('#cierre-neto-transferencia').text('-' + fmt(t.neto_transferencia));
        $('#cierre-caja-valor').text(fmt(t.cierre_caja));

        // Cierre Tecnoelectro
        $('#cierre-apertura-tecno').text(fmt(t.apertura_tecnoelectro));
        $('#cierre-ing-tecno').text('+' + fmt(t.ingreso_tecnoelectro));
        $('#cierre-egr-tecno').text('-' + fmt(t.egreso_tecnoelectro));
        $('#cierre-deposito-tecno').text('-' + fmt(t.deposito_banco_tecnoelectro));
        $('#cierre-neto-credito-tecno').text('-' + fmt(t.neto_credito_debito_tecno));
        $('#cierre-tecno-valor').text(fmt(t.cierre_tecnoelectro));

        // Transbank
        var netoTransbank = t.ingreso_transbank - t.egreso_transbank;
        $('#resumen-ing-transbank').text('+' + fmt(t.ingreso_transbank));
        $('#resumen-egr-transbank').text('-' + fmt(t.egreso_transbank));
        $('#resumen-neto-transbank').text(fmt(netoTransbank));
        $('#transbank-total-monto').text(fmt(netoTransbank));

        // KPI cards
        var totalIng = (t.total_ingresos || 0) + (t.total_ingresos_tecno || 0) + (t.ingreso_transbank || 0);
        var totalEgr = (t.total_egresos  || 0) + (t.total_egresos_tecno  || 0) + (t.egreso_transbank  || 0);
        $('#kpi-total-ing').text('+' + fmt(totalIng));
        $('#kpi-total-egr').text('−' + fmt(totalEgr));

        // Bottom bar
        $('#bb-cierre-cc').text(fmt(t.cierre_caja));
        $('#bb-cierre-tecno').text(fmt(t.cierre_tecnoelectro));
        $('#bb-neto-tb').text(fmt(netoTransbank));

        // KPI apertura display
        $('#kpi-apertura-caja-display').text(fmt(t.apertura_caja));
        $('#kpi-apertura-tecno-display').text(fmt(t.apertura_tecnoelectro));
    }

    var mediosTecno     = ['efectivo_tecno', 'credito_debito_tecno', 'devolucion_abono', 'deposito_banco_tecnoelectro'];
    var mediosTransbank = ['transbank'];

    function medioClass(medio) {
        var map = {
            efectivo: 'ef', credito_debito: 'cd', transferencia: 'tr', deposito_banco: 'dp',
            efectivo_tecno: 'ef', credito_debito_tecno: 'cd', devolucion_abono: 'dv',
            deposito_banco_tecnoelectro: 'dp', transbank: 'tb'
        };
        return map[medio] || 'oth';
    }

    function agregarFilaMovimiento(m) {
        var esTecno     = mediosTecno.indexOf(m.medio) !== -1;
        var esTransbank = mediosTransbank.indexOf(m.medio) !== -1;
        var tbodyId = esTransbank ? '#tbody-movimientos-transbank' : (esTecno ? '#tbody-movimientos-tecno' : '#tbody-movimientos');
        var filaCero = esTransbank ? '#fila-vacia-transbank' : (esTecno ? '#fila-vacia-tecno' : '#fila-vacia');

        $(filaCero).remove();

        var pillClass = m.tipo_movimiento === 'ingreso' ? 'ing' : 'egr';
        var pillIcon  = m.tipo_movimiento === 'ingreso' ? 'fa-arrow-up' : 'fa-arrow-down';
        var pillLabel = m.tipo_movimiento === 'ingreso' ? 'Ingreso' : 'Egreso';
        var signo     = m.tipo_movimiento === 'ingreso' ? '+' : '−';
        var montoClass = m.tipo_movimiento === 'ingreso' ? 'up' : 'down';
        var btnAnular = FlujoCaja.cajaAbierta
            ? '<button class="row-act btn-anular" data-id="' + m.id + '" title="Anular"><i class="fas fa-times"></i></button>'
            : '';
        var extra = FlujoCaja.cajaAbierta ? '<td>' + btnAnular + '</td>' : '';

        $(tbodyId).append(
            '<tr id="fila-' + m.id + '">' +
            '<td class="hora">' + m.created_at + '</td>' +
            '<td><span class="tipo-pill ' + pillClass + '"><i class="fas ' + pillIcon + '"></i> ' + pillLabel + '</span></td>' +
            '<td><span class="medio-tag"><span class="md ' + medioClass(m.medio) + '"></span>' + m.medio_label + '</span></td>' +
            '<td>' + (m.detalle || '—') + '</td>' +
            '<td class="r monto ' + montoClass + '">' + signo + fmt(m.monto) + '</td>' +
            '<td style="font-size:12.5px;color:var(--ink-3)">' + m.usuario + '</td>' +
            '<td><span class="estado-tag activo">Activo</span></td>' +
            extra +
            '</tr>'
        );

        // Update tab badge count
        var tabMap = esTransbank ? 'tb' : (esTecno ? 'tecno' : 'cc');
        var $tab = $('.mv-tab[data-tab="' + tabMap + '"] .badge-count');
        $tab.text(parseInt($tab.text() || 0) + 1);
    }

    // -----------------------------------------------------------------------
    // Tabs
    // -----------------------------------------------------------------------
    $('#mv-tabs').on('click', '.mv-tab', function () {
        $('#mv-tabs .mv-tab').removeClass('active');
        $(this).addClass('active');
        var which = $(this).data('tab');
        $('.mv-pane').hide();
        $('.mv-pane[data-pane="' + which + '"]').show();
    });

    // -----------------------------------------------------------------------
    // Ingreso/Egreso toggle
    // -----------------------------------------------------------------------
    $('#toggle-tipo').on('click', 'button', function () {
        var val = $(this).data('v');
        $('#toggle-tipo button').removeClass('on ing egr');
        $('#toggle-tipo button[data-v="ingreso"]').addClass('ing');
        $('#toggle-tipo button[data-v="egreso"]').addClass('egr');
        $(this).addClass('on');
        $('#hidden-tipo').val(val);
    });

    // -----------------------------------------------------------------------
    // Prev / Next day buttons
    // -----------------------------------------------------------------------
    $('#btn-prev-day').on('click', function () {
        var d = new Date($('#selector-fecha').val() + 'T12:00:00');
        d.setDate(d.getDate() - 1);
        $('#selector-fecha').val(d.toISOString().slice(0, 10)).trigger('change');
    });
    $('#btn-next-day').on('click', function () {
        var d = new Date($('#selector-fecha').val() + 'T12:00:00');
        d.setDate(d.getDate() + 1);
        var today = new Date(); today.setHours(12);
        if (d <= today) $('#selector-fecha').val(d.toISOString().slice(0, 10)).trigger('change');
    });

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
                    // Reset toggle visual
                    $('#toggle-tipo button').removeClass('on');
                    $('#toggle-tipo button[data-v="ingreso"]').addClass('on ing');
                    $('#toggle-tipo button[data-v="egreso"]').addClass('egr');
                    $('#hidden-tipo').val('ingreso');
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
        $.ajax({
            url: FlujoCaja.urlAnularBase + '/' + id + '/anular',
            method: 'POST',
            data: {_token: FlujoCaja.csrfToken},
            success: function (res) {
                if (res.success) {
                    $(fila).addClass('text-muted');
                    $(fila).find('.tipo-pill').attr('class', 'tipo-pill neu').html('<i class="fas fa-minus"></i> ' + $(fila).find('.tipo-pill').text().trim());
                    $(fila).find('.monto').removeClass('up down');
                    $(fila).find('.estado-tag').attr('class', 'estado-tag anulado').text('Anulado');
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

    // -----------------------------------------------------------------------
    // Live search across visible table pane
    // -----------------------------------------------------------------------
    $('#mv-search').on('input', function () {
        var q = $(this).val().toLowerCase();
        var $activePane = $('.mv-pane:visible');
        $activePane.find('tbody tr').each(function () {
            var text = $(this).text().toLowerCase();
            $(this).toggle(!q || text.indexOf(q) !== -1);
        });
    });

});
</script>
@endpush
