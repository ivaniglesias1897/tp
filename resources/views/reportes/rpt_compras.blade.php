@extends('layouts.app')

@section('content')
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Reportes Compras</h1>
                </div>
            </div>
        </div>
    </section>

    <div class="content px-3">
        <div class="card">
            <div class="card-body p-3">
                <div class="row">
                    <div class="form-group col-sm-3">
                        {!! Form::label('proveedor', 'Proveedor:') !!}
                        {{-- La variable del controlador es $proveedores --}}
                        {!! Form::select('proveedor', $proveedores, request()->get('proveedor', null), [
                            'class' => 'form-control select2',
                            'placeholder' => 'Seleccione',
                            'id' => 'proveedores', // ID utilizado en JS
                        ]) !!}
                    </div>

                    <div class="form-group col-sm-3">
                        {!! Form::label('desde', 'Desde:') !!}
                        {!! Form::date('desde', request()->get('desde', null), ['class' => 'form-control', 'id' => 'desde']) !!}
                    </div>

                    <div class="form-group col-sm-3">
                        {!! Form::label('hasta', 'Hasta:') !!}
                        {!! Form::date('hasta', request()->get('hasta', null), ['class' => 'form-control', 'id' => 'hasta']) !!}
                    </div>

                    <div class="form-group col-sm-3">
                        <button class="btn btn-success" type="button" title="Consultar / Buscar" id="btn-consultar"
                            style="margin-top:30px">
                            <i class="fas fa fa-search"></i>
                        </button>

                        <button class="btn btn-default" type="button" 
                            style="margin-top:30px"
                            id="btn-limpiar"
                            data-toggle="tooltip" data-placement="top"
                            title="Limpiar">
                            <i class="fas fa-eraser"></i>
                        </button>

                        <button class="btn btn-primary" id="btn-exportar" type="button" data-toggle="tooltip"
                            title="Exportar a PDF" style="margin-top:30px">
                            <i class="fas fa-print"></i> PDF
                        </button>
                        <button class="btn btn-success" id="btn-exportar-excel" type="button" data-toggle="tooltip"
                            title="Exportar a Excel" style="margin-top:30px; background-color: #28a745; border-color: #28a745; color: white;">
                            <i class="fas fa-file-excel"></i> Excel
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table" id="compras-table">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Proveedor</th>
                                <th>Fecha</th>
                                <th>Condición</th>
                                <th>Total</th>
                                <th>Estado</th>
                                <th>Nro Factura</th>
                                <th>Sucursal</th>
                                <th>Usuario</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($compras as $compra)
                                <tr>
                                    <td>{{ $compra->id_compra }}</td>
                                    <td>{{ $compra->proveedor }}</td>
                                    <td>{{ \Carbon\Carbon::parse($compra->fecha_compra)->format('d/m/Y') }}</td>
                                    <td>{{ $compra->condicion_compra }}</td>
                                    <td>{{ number_format($compra->total, 0, ',', '.') }}</td>
                                    <td>{{ $compra->estado }}</td>
                                    {{-- El alias del controlador es factura_nro --}}
                                    <td>{{ $compra->factura_nro }}</td> 
                                    <td>{{ $compra->sucursal }}</td>
                                    <td>{{ $compra->usuario }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts') 
    <script type="text/javascript">
        $(document).ready(function() {
            // ** VALORES INYECTADOS POR BLADE **
            const ROUTE_REPORTES = '{{ route('reportes.compras') }}'; 
            
            // Usamos una cadena 'null' para la lógica de focus si no hay valor de filtro
            const PROVEEDOR_VAL = '{{ request()->get('proveedor', 'null') }}'; 
            const DESDE_VAL = '{{ request()->get('desde', 'null') }}';
            const HASTA_VAL = '{{ request()->get('hasta', 'null') }}';


            // Función para construir los parámetros de la URL de forma robusta
            function buildQueryString(exportType = null) {
                let params = new URLSearchParams();
                
                // Obtenemos los valores actuales de los filtros
                let proveedor = $("#proveedores").val();
                let desde = $("#desde").val();
                let hasta = $("#hasta").val();
                
                // Solo adjuntamos parámetros si tienen valor (evitando ?proveedor=)
                if (proveedor) params.append('proveedor', proveedor);
                if (desde) params.append('desde', desde);
                if (hasta) params.append('hasta', hasta);
                if (exportType) params.append('exportar', exportType);
                
                // Retorna la URL base con los parámetros
                return ROUTE_REPORTES + '?' + params.toString();
            }

            // 1. Botón Limpiar: Redirige a la ruta base sin parámetros
            $('#btn-limpiar').click(function(e) {
                e.preventDefault();
                // Redirigimos a la URL base, borrando cualquier filtro
                window.location.href = ROUTE_REPORTES;
            });

            // 2. Botón Consultar / Buscar
            $("#btn-consultar").click(function(e) {
                e.preventDefault();
                window.location.href = buildQueryString();
            });

            // 3. Botón Exportar PDF
            $("#btn-exportar").click(function(e) {
                e.preventDefault();
                window.open(buildQueryString('pdf'), '_blank');
            });

            // 4. Botón Exportar Excel
            $("#btn-exportar-excel").click(function(e) {
                e.preventDefault();
                window.open(buildQueryString('excel'), '_blank');
            });

            // 5. Lógica para el Focus (si la página carga sin filtros)
            if (PROVEEDOR_VAL === 'null' && DESDE_VAL === 'null' && HASTA_VAL === 'null') {
                $('#proveedores').focus();
            }
        });
    </script>
@endpush