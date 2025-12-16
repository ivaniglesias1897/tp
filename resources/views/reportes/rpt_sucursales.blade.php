@extends('layouts.app')

@section('content')
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Reporte de Sucursales</h1>
                </div>
            </div>
        </div>
    </section>

    <div class="content px-3">
        <div class="clearfix"></div>
        <!-- Card de filtros -->
        <div class="card">
            <div class="card-body p-3">
                <h3>Filtros</h3>
                <div class="row">
                    <div class="form-group col-sm-3">
                        {!! Form::label('desde', 'Código Desde:') !!}
                        {!! Form::text('desde', request()->get('desde', null), [
                            'class' => 'form-control',
                            'placeholder' => 'Ingrese el código',
                            'id' => 'desde',
                        ]) !!}
                    </div>

                    <div class="form-group col-sm-3">
                        {!! Form::label('hasta', 'Código Hasta:') !!}
                        {!! Form::text('hasta', request()->get('hasta', null), [
                            'class' => 'form-control',
                            'placeholder' => 'Ingrese el código',
                            'id' => 'hasta',
                        ]) !!}
                    </div>

                    <div class="form-group col-sm-6">
                        <button class="btn btn-success" type="button" data-toggle="tooltip" data-placement="top"
                            title="Buscar" id="btn-consultar" style="margin-top:32px">
                            <i class="fas fa fa-search"></i>
                        </button>

                        <button class="btn btn-default" type="button" data-toggle="tooltip" title="Limpiar"
                            id="btn-limpiar" style="margin-top:32px">
                            <i class="fas fa fa-eraser"></i>
                        </button>

                        <button class="btn btn-primary" id="btn-exportar" type="button" data-toggle="tooltip"
                            title="Exportar" style="margin-top:32px">
                            <i class="fas fa-print"></i> PDF
                        </button>
                        
                        <!-- NUEVO BOTÓN EXCEL -->
                        <button class="btn btn-success" id="btn-exportar-excel" type="button" data-toggle="tooltip"
                            title="Exportar a Excel" style="margin-top:32px; background-color: #28a745; border-color: #28a745; color: white;">
                            <i class="fas fa-file-excel"></i> Excel
                        </button>
                    </div>
                </div>
            </div>
        </div>
        <!-- Fin filtros -->

        <div class="card">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table" id="sucursales-table">
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
        </div>
    </div>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        $(document).ready(function() {
            // Inicializar tooltips
            $('[data-toggle="tooltip"]').tooltip();

            // Botón de consulta
            $('#btn-consultar').click(function(e) {
                e.preventDefault();
                window.location.href = '{{ url('reporte-sucursales') }}?desde=' + $('#desde').val() +
                    '&hasta=' + $('#hasta').val();
            });

            // Botón de exportación PDF
            $('#btn-exportar').click(function(e) {
                e.preventDefault();
                window.open('{{ url('reporte-sucursales') }}?desde=' + $('#desde').val() + '&hasta=' + $(
                    '#hasta').val() + '&exportar=pdf', '_blank');
            });

            // NUEVO: Botón de exportación Excel
            $('#btn-exportar-excel').click(function(e) {
                e.preventDefault();
                window.open('{{ url('reporte-sucursales') }}?desde=' + $('#desde').val() + '&hasta=' + $(
                    '#hasta').val() + '&exportar=excel', '_blank');
            });

            // Botón de limpieza
            $('#btn-limpiar').click(function(e) {
                e.preventDefault();
                $('#desde').val('');
                $('#hasta').val('');
                window.location.href = '{{ url('reporte-sucursales') }}';
            });
        });
    </script>
@endpush