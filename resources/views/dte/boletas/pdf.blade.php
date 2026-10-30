<!DOCTYPE html>
<html>
<head>
    <meta charset="ISO-8859-1">
    <style>
        body { font-family: sans-serif; font-size: 10px; margin: 0; padding: 10px; }
        .header { text-align: center; border-bottom: 1px solid #000; padding-bottom: 5px; margin-bottom: 8px; }
        .header h2 { margin: 0; font-size: 12px; }
        .header p { margin: 2px 0; }
        .info-row { display: flex; margin-bottom: 3px; }
        .info-label { font-weight: bold; width: 80px; }
        table { width: 100%; border-collapse: collapse; margin: 8px 0; }
        th, td { padding: 3px 5px; text-align: left; border-bottom: 1px solid #ccc; }
        th { font-weight: bold; border-bottom: 2px solid #000; }
        .text-right { text-align: right; }
        .totales { margin-top: 5px; }
        .totales td { border: none; padding: 2px 5px; }
        .total-final { font-weight: bold; font-size: 12px; border-top: 2px solid #000 !important; }
        .ted-section { margin-top: 10px; text-align: center; font-size: 8px; color: #666; }
    </style>
</head>
<body>
    <div class="header">
        @if($config)
            <h2>{{ $config->razon_social }}</h2>
            <p>RUT: {{ $config->rut_empresa }}</p>
            <p>{{ $config->giro }}</p>
            <p>{{ $config->direccion }}, {{ $config->comuna }}, {{ $config->ciudad }}</p>
        @endif
        <p style="margin-top:5px; font-size:11px;"><strong>BOLETA ELECTR&Oacute;NICA</strong></p>
        <p style="font-size:14px; font-weight:bold;">N&deg; {{ $documento->folio }}</p>
    </div>

    <div>
        <table style="border:none; margin:0;">
            <tr>
                <td style="border:none; padding:1px 0;"><strong>Fecha:</strong> {{ $documento->fecha_emision->format('d/m/Y') }}</td>
            </tr>
            <tr>
                <td style="border:none; padding:1px 0;"><strong>RUT:</strong> {{ $documento->rut_receptor }}</td>
            </tr>
            <tr>
                <td style="border:none; padding:1px 0;"><strong>Sr(a):</strong> {{ $documento->razon_social_receptor }}</td>
            </tr>
        </table>
    </div>

    <table>
        <thead>
            <tr>
                <th>Item</th>
                <th>Cant.</th>
                <th class="text-right">P.Unit.</th>
                <th class="text-right">Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach($documento->detalles as $detalle)
            <tr>
                <td>{{ $detalle->nombre_item }}</td>
                <td>{{ $detalle->cantidad }}</td>
                <td class="text-right">${{ number_format($detalle->precio_unitario, 0, ',', '.') }}</td>
                <td class="text-right">${{ number_format($detalle->monto_item, 0, ',', '.') }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <table class="totales">
        <tr>
            <td class="text-right">Neto:</td>
            <td class="text-right" style="width:100px">${{ number_format($documento->monto_neto, 0, ',', '.') }}</td>
        </tr>
        <tr>
            <td class="text-right">IVA (19%):</td>
            <td class="text-right">${{ number_format($documento->monto_iva, 0, ',', '.') }}</td>
        </tr>
        <tr class="total-final">
            <td class="text-right">TOTAL:</td>
            <td class="text-right">${{ number_format($documento->monto_total, 0, ',', '.') }}</td>
        </tr>
    </table>

    <div class="ted-section">
        <p>Timbre Electr&oacute;nico SII</p>
        <p style="word-break:break-all; font-size:7px;">{{ $tedBase64 }}</p>
    </div>
</body>
</html>
