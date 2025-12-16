<html>

<head>
    <title>Reporte de venta</title>
    <style>
        @page {
            margin: 0cm 0cm;
            margin-bottom: 2cm;
        }

        body {
            margin-top: 1cm;
            margin-left: 1cm;
            margin-right: 1cm;
            margin-bottom: 1cm;
        }

        .tabla {
            font-family: "Trebuchet MS", Arial, Helvetica, sans-serif;
            border-collapse: collapse;
            width: 100%;
            border: 0px solid #ddd;
        }

        .tabla td,
        .tabla th {
            border: 0px solid #ddd;
            padding: 2px;
        }

        .tabla tr:nth-child(even) {
            background-color: #f2f2f2;
        }

        .tabla tr:hover {
            background-color: #ddd;
        }

        .tabla th {
            padding-top: 3px;
            padding-bottom: 3px;
            /*text-align: left;*/
            background-color: #f6efef;
            color: black;
        }

        tr.was-replaced td {
            text-decoration: line-through;
        }

        th {
            font-size: 12px;
            font-weight: bold;
            padding-left: 5px;
            padding-bottom: 2px;
        }

        td {
            font-size: 12px;
            padding-left: 5px;
            padding-bottom: 2px;
        }

        .center {
            text-align: center;
        }
    </style>
</head>

<body>
    <div class="box box-primary">
        <p style="text-align: center;">
            <b>Reporte de venta<b>
        </p>
        <br>
        <div class="box-body">
            <table class="tabla">
                <thead>
                    @foreach ($ventas as $venta)
                        <tr>
                            <td colspan="2"><b>Fecha:</b>
                                {{ \Carbon\Carbon::parse($venta->fecha_venta)->format('d/m/Y') }}</td>
                            <td><b>Cliente:</b> {{ $venta->cliente }}</td>
                            <td><b>Condición Venta:</b> {{ $venta->condicion_venta }}</td>
                        </tr>

                        @if (!empty($venta->intervalo))
                            <tr>
                                <td colspan="2"><b>Intervalo Vto:</b> {{ $venta->intervalo }}</td>
                                <td><b>Cantidad Cuota:</b> {{ $venta->cantidad_cuota }}</td>
                            </tr>
                        @endif
                        <tr>
                            <td colspan="2"><b>Sucursal:< {{ $venta->sucursal }}</td>
                            <td><b>Factura Nro: {{ $venta->factura_nro }}</td>
                            <td><b>Estado: {{ $venta->estado }}</td>
                            <td><b>Vendedor: {{ $venta->usuario }}</td>
                        </tr>
                        <tr>
                            <td>Total: {{ number_format($venta->total, 0, ',', '.') }}</td>
                        </tr>

                        <!-- detalle de ventas -->
                        <tr style="border: 1px; color:#000; background: #C5C9D3">
                            <td>Código Producto</td>
                            <td>Descripción</td>
                            <td>Cantidad</td>
                            <td>Precio Unit</td>
                            <td>Subtotal</td>
                        </tr>
                        <!-- usar forelse para mostrar mensaje si no hay detalles -->
                        @forelse ($array_detalles[$venta->id_venta] as $key => $det)
                            <tr>
                                <td>{{ $det->id_producto }}</td>
                                <td>{{ $det->producto }}</td>
                                <td>{{ $det->cantidad }}</td>
                                <td>{{ number_format($det->precio, 0, ',', '.') }}</td>
                                <td>{{ number_format($det->precio * $det->cantidad, 0, ',', '.') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" style="text-align: center"><b>No existe detalle para la venta..</b>
                                </td>
                            </tr>
                        @endforelse
                        <!-- salto de linea entre ventas -->
                        <br>
                        <tr><!-- imprimir una linea horizontal entre ventas -->
                            <td colspan="5">
                                <hr>
                            </td>
                        </tr>
                    @endforeach
                </thead>
            </table>
        </div>
    </div>
</body>

</html>
