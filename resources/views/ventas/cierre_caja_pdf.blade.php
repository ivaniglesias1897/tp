<html>

<head>
    <title>Cierre Caja</title>
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
            border: 1px solid black;
        }

        .tabla td,
        .tabla th {
            border: 1px solid #ddd;
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
            <b>Cierre Caja<b>
        </p>
        <br>
        <div class="box-body">
            <table class="tabla">
                <thead>
                    @php
                        ##definir variables
                        $totales = 0;
                    @endphp
                    <tr>
                        <td colspan="2"><b>Fecha:</b>
                            {{ \Carbon\Carbon::parse($cierre_caja->fecha_apertura)->format('d/m/Y') }}
                        </td>
                        <td><b>Usuario:</b> {{ $cierre_caja->usuario }}</td>
                        <td><b>Sucursal:</b> {{ $cierre_caja->sucursal }}</td>
                        <td><b>Caja:</b> {{ $cierre_caja->caja }}</td>
                    </tr>
                </thead>
            </table>
            <br>
            <table class="tabla">
                <thead>
                    <tr>
                        <th colspan="2" class="center">Totales por Forma de Pago</th>
                    </tr>
                    
                    <tr>
                        <td><b>FORMA DE PAGOS</b></td>
                        <td class="center"><b>COBRADO</b></td>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($totales_forma_pago as $value)
                        <!-- calcular el total de los totales por forma de pago -->
                        @php
                            $totales += $value->total_cobro;
                        @endphp
                        <tr>
                            <td>{{ $value->forma_pago }}</td>
                            <td class="center">{{ number_format($value->total_cobro, 0, ',', '.') }}</td>
                        </tr>
                    @endforeach

                    <tfoot>
                        <tr>
                            <th>TOTAL</th>
                            <th>{{ number_format($totales, 0, ',', '.') }}</th>
                        </tr>
                    </tfoot>
                </tbody>
            </table>

            <div style="margin-top: 20px;"></div>
                <p>Cajero:   ...................................... </p>
                <p>Administración:  ...............................</p>
            </div>

        </div>
    </div>
</body>

</html>