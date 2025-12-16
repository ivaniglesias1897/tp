<html>

<head>
    <title>Reporte de Clientes</title>
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
            <b>Reporte de Clientes<b>
        </p>
        <br>
        <div class="box-body">
            <!-- se crea un archivo table_clientes y lo incluimos en los blade a utilizar. Tambien se debe compartir la variable que utiliza el table_clientes -->
            @includeIf('reportes.table_clientes', ['clientes' => $clientes])
        </div>
    </div>
</body>

</html>
