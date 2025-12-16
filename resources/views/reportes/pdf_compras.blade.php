<html>

<head>
    <title>Reporte de Compras</title>
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
            <b>Reporte de Compras</b>
        </p>
        <br>
        <div class="box-body">
            <table class="tabla">
                <thead>
                    {{-- Iteramos sobre el listado de compras --}}
                    @foreach ($compras as $compra)
                        <tr>
                            <td colspan="2"><b>Fecha Compra:</b>
                                {{ \Carbon\Carbon::parse($compra->fecha_compra)->format('d/m/Y') }}</td>
                            <td><b>Proveedor:</b> {{ $compra->proveedor }}</td>
                            <td><b>Condición Compra:</b> {{ $compra->condicion_compra }}</td>
                        </tr>

                        {{-- Si es una compra a crédito, mostramos los detalles de las cuotas --}}
                        @if (!empty($compra->intervalo))
                            <tr>
                                <td colspan="2"><b>Intervalo Vto:</b> {{ $compra->intervalo }} días</td>
                                <td><b>Cantidad Cuota:</b> {{ $compra->cantidad_cuotas }}</td>
                            </tr>
                        @endif
                        
                        <tr>
                            <td colspan="2"><b>Sucursal:</b> {{ $compra->sucursal }}</td>
                            <td><b>Factura Nro:</b> {{ $compra->factura_nro }}</td>
                            <td><b>Estado:</b> {{ $compra->estado }}</td>
                            <td><b>Usuario:</b> {{ $compra->usuario }}</td>
                        </tr>
                        <tr>
                            <td>Total: {{ number_format($compra->total, 0, ',', '.') }}</td>
                        </tr>

                        <tr style="border: 1px; color:#000; background: #C5C9D3">
                            <td>Código Producto</td>
                            <td>Descripción</td>
                            <td>Cantidad</td>
                            <td>Costo Unit.</td>
                            <td>Subtotal</td>
                        </tr>
                        {{-- Usamos $array_detalles que viene del controlador --}}
                        @forelse ($array_detalles[$compra->id_compra] as $key => $det)
                            <tr>
                                <td>{{ $det->id_producto }}</td>
                                <td>{{ $det->producto }}</td>
                                <td>{{ $det->cantidad }}</td>
                                <td>{{ number_format($det->costo, 0, ',', '.') }}</td>
                                <td>{{ number_format($det->costo * $det->cantidad, 0, ',', '.') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" style="text-align: center"><b>No existe detalle para esta compra.</b>
                                </td>
                            </tr>
                        @endforelse
                        <br>
                        <tr>
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