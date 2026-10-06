@php
    $estados = [
        'pendiente'   => 'Pendiente',
        'en_progreso' => 'En progreso',
        'finalizada'  => 'Finalizada',
        'cancelada'   => 'Cancelada',
    ];
    $tiposServicio = [
        'mantenimiento' => 'Mantenimiento',
        'reparacion'    => 'Reparación',
        'instalacion'   => 'Instalación',
        'garantia'      => 'Garantía',
    ];
    $tiposAsistencia = [
        'garantia_extendida' => 'Garantía extendida',
        'garantia_fabrica'   => 'Garantía fábrica',
        'garantia_trabajo'   => 'Garantía de trabajo',
        'fuera_garantia'     => 'Fuera de garantía',
    ];

    $artefacto = $orden->artefacto;
    $artefactoLabel = null;
    if ($artefacto) {
        if ($artefacto->marca && $artefacto->modelo) {
            $artefactoLabel = $artefacto->marca . ' ' . $artefacto->modelo;
        } elseif ($artefacto->modelo) {
            $artefactoLabel = $artefacto->modelo;
        } elseif ($artefacto->marca) {
            $artefactoLabel = $artefacto->marca . ($artefacto->descripcion ? ' – ' . $artefacto->descripcion : '');
        } else {
            $artefactoLabel = $artefacto->descripcion;
        }
    }

    $subtotalDetalles = $orden->detalles->sum('subtotal');
    $money = function ($valor) {
        return '$' . number_format((float) $valor, 0, ',', '.');
    };
@endphp
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Orden de Servicio N° {{ $orden->numero }}</title>
    <style>
        @page { margin: 32px 36px 56px 36px; }

        * { box-sizing: border-box; }
        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 9.5px;
            color: #1f2937;
            margin: 0;
        }

        .brand  { color: #132a56; }
        .accent { color: #f7941d; }
        .muted  { color: #6b7280; }
        .right  { text-align: right; }
        .center { text-align: center; }
        .bold   { font-weight: bold; }

        table { width: 100%; border-collapse: collapse; }
        td, th { vertical-align: top; }

        /* ── Encabezado ─────────────────────────────────────────── */
        .header td { vertical-align: middle; }
        .header .logo { width: 70px; }
        .header .logo img { width: 62px; }
        .company-name { font-size: 16px; font-weight: bold; color: #132a56; }
        .company-sub  { font-size: 9px; color: #6b7280; margin-top: 2px; }

        .folio-box {
            border: 1.5px solid #132a56;
            border-radius: 6px;
            width: 200px;
            text-align: center;
            margin-left: auto;
        }
        .folio-box .title {
            background: #132a56;
            color: #ffffff;
            font-size: 10px;
            font-weight: bold;
            letter-spacing: 1px;
            padding: 5px 0;
            border-radius: 4px 4px 0 0;
        }
        .folio-box .number {
            font-size: 18px;
            font-weight: bold;
            color: #132a56;
            padding: 4px 0 2px;
        }
        .folio-box .date { font-size: 8.5px; color: #6b7280; padding-bottom: 5px; }

        .header-rule {
            height: 3px;
            background: #132a56;
            border-bottom: 2px solid #f7941d;
            margin: 10px 0 12px;
        }

        /* ── Secciones ──────────────────────────────────────────── */
        .section { margin-bottom: 10px; }
        .section-title {
            font-size: 9.5px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.6px;
            color: #132a56;
            border-bottom: 1px solid #d1d5db;
            padding-bottom: 3px;
            margin-bottom: 5px;
        }
        .section-title span { color: #f7941d; }

        .info td { padding: 2px 4px 2px 0; }
        .info .label { width: 85px; color: #6b7280; }
        .info .value { font-weight: bold; color: #111827; }

        .panel {
            border: 1px solid #e5e7eb;
            border-radius: 4px;
            padding: 6px 8px;
            background: #f9fafb;
            min-height: 22px;
        }

        .badge {
            display: inline-block;
            padding: 1px 7px;
            border-radius: 8px;
            font-size: 8.5px;
            font-weight: bold;
            color: #ffffff;
        }
        .badge-pendiente   { background: #d97706; }
        .badge-en_progreso { background: #2563eb; }
        .badge-finalizada  { background: #16a34a; }
        .badge-cancelada   { background: #dc2626; }

        /* ── Tabla de detalle ───────────────────────────────────── */
        .items th {
            background: #132a56;
            color: #ffffff;
            font-size: 8.5px;
            text-transform: uppercase;
            padding: 5px 6px;
            text-align: left;
        }
        .items th.center { text-align: center; }
        .items th.right  { text-align: right; }
        .items td {
            padding: 5px 6px;
            border-bottom: 1px solid #e5e7eb;
        }
        .items tr:nth-child(even) td { background: #f9fafb; }
        .items .nota { font-size: 8px; color: #6b7280; margin-top: 1px; }
        .tag {
            font-size: 7.5px;
            color: #132a56;
            border: 1px solid #132a56;
            border-radius: 3px;
            padding: 0 3px;
        }

        .totals { width: 230px; margin-left: auto; margin-top: 6px; }
        .totals td { padding: 3px 6px; }
        .totals .grand td {
            background: #132a56;
            color: #ffffff;
            font-size: 11px;
            font-weight: bold;
            padding: 6px;
        }

        /* ── Comentarios manuscritos y firmas ───────────────────── */
        .keep-together { page-break-inside: avoid; }

        .handwrite {
            border: 1px solid #9ca3af;
            border-radius: 4px;
            padding: 4px 10px 8px;
        }
        .handwrite .line {
            border-bottom: 1px dotted #9ca3af;
            height: 22px;
        }

        .signatures { margin-top: 34px; }
        .signatures td { width: 50%; padding: 0 18px; text-align: center; }
        .sign-line { border-top: 1px solid #374151; padding-top: 4px; font-weight: bold; }
        .sign-sub  { font-size: 8px; color: #6b7280; margin-top: 2px; text-align: left; }

        /* ── Pie de página ──────────────────────────────────────── */
        .footer {
            position: fixed;
            bottom: -38px;
            left: 0;
            right: 0;
            height: 24px;
            border-top: 1px solid #e5e7eb;
            padding-top: 5px;
            font-size: 7.5px;
            color: #9ca3af;
        }
        .pagenum:before { content: counter(page); }
    </style>
</head>
<body>

    <div class="footer">
        <table>
            <tr>
                <td>{{ $concesion->name ?? '' }} · Orden de Servicio N° {{ $orden->numero }} · Generado el {{ now()->format('d/m/Y H:i') }}</td>
                <td class="right">Página <span class="pagenum"></span></td>
            </tr>
        </table>
    </div>

    {{-- ── Encabezado ─────────────────────────────────────────────── --}}
    <table class="header">
        <tr>
            @if($logo)
                <td class="logo"><img src="{{ $logo }}" alt="Logo"></td>
            @endif
            <td>
                <div class="company-name">{{ $concesion->name ?? 'Servicio Técnico' }}</div>
                @if(!empty($concesion->address))
                    <div class="company-sub">{{ $concesion->address }}</div>
                @endif
                <div class="company-sub">Servicio técnico de artefactos</div>
            </td>
            <td style="width: 210px;">
                <div class="folio-box">
                    <div class="title">ORDEN DE SERVICIO</div>
                    <div class="number">N° {{ str_pad($orden->numero, 6, '0', STR_PAD_LEFT) }}</div>
                    <div class="date">
                        Emitida el {{ $orden->fecha_orden ? $orden->fecha_orden->format('d/m/Y H:i') : '-' }}
                    </div>
                </div>
            </td>
        </tr>
    </table>
    <div class="header-rule"></div>

    {{-- ── Cliente y Servicio ─────────────────────────────────────── --}}
    <table>
        <tr>
            <td style="width: 50%; padding-right: 10px;">
                <div class="section">
                    <div class="section-title"><span>■</span> Datos del cliente</div>
                    <table class="info">
                        <tr>
                            <td class="label">Nombre</td>
                            <td class="value">{{ $orden->cliente->nombre ?? '' }} {{ $orden->cliente->apellido ?? '' }}</td>
                        </tr>
                        @if(!empty($orden->cliente->rut))
                            <tr><td class="label">RUT</td><td class="value">{{ $orden->cliente->rut }}</td></tr>
                        @endif
                        <tr>
                            <td class="label">Dirección</td>
                            <td class="value">
                                {{ $orden->cliente->direccion ?? '-' }}{{ !empty($orden->cliente->ciudad) ? ', ' . $orden->cliente->ciudad : '' }}
                            </td>
                        </tr>
                        <tr><td class="label">Teléfono</td><td class="value">{{ $orden->cliente->numero_contacto ?? '-' }}</td></tr>
                        @if(!empty($orden->cliente->email))
                            <tr><td class="label">Email</td><td class="value">{{ $orden->cliente->email }}</td></tr>
                        @endif
                    </table>
                </div>
            </td>
            <td style="width: 50%; padding-left: 10px;">
                <div class="section">
                    <div class="section-title"><span>■</span> Datos del servicio</div>
                    <table class="info">
                        <tr>
                            <td class="label">Estado</td>
                            <td class="value">
                                <span class="badge badge-{{ $orden->estado }}">{{ $estados[$orden->estado] ?? ucfirst($orden->estado) }}</span>
                            </td>
                        </tr>
                        @if($orden->tipo_servicio)
                            <tr>
                                <td class="label">Tipo servicio</td>
                                <td class="value">{{ $tiposServicio[$orden->tipo_servicio] ?? ucfirst($orden->tipo_servicio) }}</td>
                            </tr>
                        @endif
                        <tr>
                            <td class="label">Atención</td>
                            <td class="value">{{ $orden->tipo_atencion === 'terreno' ? 'En terreno (domicilio)' : 'En taller' }}</td>
                        </tr>
                        @if($orden->tipo_asistencia)
                            <tr>
                                <td class="label">Asistencia</td>
                                <td class="value">{{ $tiposAsistencia[$orden->tipo_asistencia] ?? $orden->tipo_asistencia }}</td>
                            </tr>
                        @endif
                        @if($orden->folio_garantia)
                            <tr><td class="label">Folio garantía</td><td class="value">{{ $orden->folio_garantia }}</td></tr>
                        @endif
                        @if($orden->fecha_visita)
                            <tr><td class="label">Fecha visita</td><td class="value">{{ $orden->fecha_visita->format('d/m/Y H:i') }}</td></tr>
                        @endif
                        <tr>
                            <td class="label">Técnico</td>
                            <td class="value">{{ $orden->tecnico->nombre ?? 'Sin asignar' }}</td>
                        </tr>
                    </table>
                </div>
            </td>
        </tr>
    </table>

    {{-- ── Artefacto ──────────────────────────────────────────────── --}}
    @if($artefacto)
        <div class="section">
            <div class="section-title"><span>■</span> Artefacto</div>
            <table class="info">
                <tr>
                    @if($artefacto->tipoArtefacto)
                        <td class="label">Tipo</td>
                        <td class="value">{{ $artefacto->tipoArtefacto->nombre }}</td>
                    @endif
                    <td class="label">Equipo</td>
                    <td class="value">{{ $artefactoLabel ?: '-' }}</td>
                    @if($artefacto->codigo)
                        <td class="label">Código</td>
                        <td class="value">{{ $artefacto->codigo }}</td>
                    @endif
                </tr>
            </table>
        </div>
    @endif

    {{-- ── Falla y observaciones ──────────────────────────────────── --}}
    <div class="section">
        <div class="section-title"><span>■</span> Descripción de la falla</div>
        <div class="panel">{!! nl2br(e($orden->descripcion_falla)) !!}</div>
    </div>

    @if($orden->observaciones)
        <div class="section">
            <div class="section-title"><span>■</span> Observaciones</div>
            <div class="panel">{!! nl2br(e($orden->observaciones)) !!}</div>
        </div>
    @endif

    {{-- ── Detalle de productos y servicios ───────────────────────── --}}
    <div class="section">
        <div class="section-title"><span>■</span> Detalle de trabajos y repuestos</div>
        <table class="items">
            <thead>
                <tr>
                    <th style="width: 22px;" class="center">#</th>
                    <th>Descripción</th>
                    <th style="width: 50px;" class="center">Cant.</th>
                    <th style="width: 80px;" class="right">P. unitario</th>
                    <th style="width: 85px;" class="right">Subtotal</th>
                </tr>
            </thead>
            <tbody>
                @forelse($orden->detalles as $i => $detalle)
                    <tr>
                        <td class="center muted">{{ $i + 1 }}</td>
                        <td>
                            @if($detalle->producto)
                                {{ $detalle->producto->name }} <span class="tag">Repuesto</span>
                            @elseif($detalle->servicio)
                                {{ $detalle->servicio->nombre_servicio }} <span class="tag">Servicio</span>
                            @else
                                <span class="muted">Ítem no disponible</span>
                            @endif
                            @if($detalle->nota)
                                <div class="nota">{{ $detalle->nota }}</div>
                            @endif
                        </td>
                        <td class="center">{{ $detalle->cantidad }}</td>
                        <td class="right">{{ $money($detalle->precio_unitario) }}</td>
                        <td class="right bold">{{ $money($detalle->subtotal) }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="center muted" style="padding: 10px;">Sin productos ni servicios registrados.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <table class="totals">
            <tr>
                <td class="muted">Subtotal ítems</td>
                <td class="right">{{ $money($subtotalDetalles) }}</td>
            </tr>
            @if($orden->valor_visita)
                <tr>
                    <td class="muted">Valor visita</td>
                    <td class="right">{{ $money($orden->valor_visita) }}</td>
                </tr>
            @endif
            <tr class="grand">
                <td>TOTAL</td>
                <td class="right">{{ $money($orden->costo_total) }}</td>
            </tr>
        </table>
    </div>

    {{-- ── Comentarios del técnico (a mano) y firmas ──────────────── --}}
    <div class="keep-together">
        <div class="section">
            <div class="section-title"><span>■</span> Comentarios del técnico</div>
            <div class="handwrite">
                @for($l = 0; $l < 5; $l++)
                    <div class="line"></div>
                @endfor
            </div>
        </div>

        <table class="signatures">
            <tr>
                <td>
                    <div class="sign-line">Firma técnico</div>
                    <div class="sign-sub">Nombre: {{ $orden->tecnico->nombre ?? '' }}</div>
                </td>
                <td>
                    <div class="sign-line">Recibí conforme · Firma cliente</div>
                    <div class="sign-sub">Nombre: ______________________________</div>
                    <div class="sign-sub">RUT: _________________ &nbsp; Fecha: ___/___/______</div>
                </td>
            </tr>
        </table>
    </div>

</body>
</html>
