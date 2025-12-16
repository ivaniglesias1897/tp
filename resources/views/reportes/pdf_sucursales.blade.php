<html>
<head>
    <title>Reporte de Sucursales</title>
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
            border: 1px solid rgb(126, 98, 98);
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
    </style>
</head>

<body>
    <div class="box box-primary">
        <p style="text-align: center;">
            <b>Reporte de Sucursales<b>
        </p>
        <br>
        <div class="box-body">
            <table class="tabla table-bordered">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Descripción</th>
                        <th>Dirección</th>
                        <th>Teléfono</th>
                        <th>Ciudad</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($sucursales as $sucursal)
                        <tr>
                            <td>{{ $sucursal->id_sucursal }}</td>
                            <td>{{ $sucursal->descripcion }}</td>
                            <td>{{ $sucursal->direccion }}</td>
                            <td>{{ $sucursal->telefono }}</td>
                            <td>{{ $sucursal->ciudad }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>