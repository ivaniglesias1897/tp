@extends('layouts.app')

@section('content')
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Reporte de Cargos</h1>
                </div>
            </div>
        </div>
    </section>

    <div class="content px-3">

        <div class="clearfix"></div>
        <!-- card de filtros -->
        <div class="card">
            <div class="card-body p-3">

                <h3>Filtros</h3>
                <div class="row">
                    <div class="form-group col-sm-3">
                        {!! Form::label('desde', 'Codigo Desde:') !!}
                        {!! Form::text('desde', request()->get('desde', null), [
                            'class' => 'form-control',
                            'placeholder' => 'Ingrese el código',
                            'id' => 'desde',
                        ]) !!}
                    </div>

                    <div class="form-group col-sm-3">
                        {!! Form::label('hasta', 'Codigo Hasta:') !!}
                        {!! Form::text('hasta', request()->get('hasta', null), [
                            'class' => 'form-control',
                            'placeholder' => 'Ingrese el código',
                            'id' => 'hasta',
                        ]) !!}
                    </div>


                    <div class="form-group col-sm-6">
                        <button class="btn btn-success" type="button" 
                            data-toggle="tooltip" data-placement="top"
                            title="Buscar" id="btn-consultar" style="margin-top:30px">
                            <i class="fas fa fa-search"></i>
                        </button>

                        <button class="btn btn-default" type="button" 
                            style="margin-top:30px"
                            id="btn-limpiar"
                            data-toggle="tooltip" data-placement="top"
                            title="Limpiar">
                            <i class="fas fa-eraser"></i>
                        </button>

                        <button class="btn btn-primary" id="btn-exportar" type="button" 
                            data-toggle="tooltip"
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
        <!-- fin filtros -->

        <div class="card">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table" id="cargos-table">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Descripcion</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($cargos as $cargo)
                                <tr>
                                    <td>{{ $cargo->id_cargo }}</td>
                                    <td>{{ $cargo->descripcion }}</td>
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
            // llamar a la función tooltip
            $('[data-toggle="tooltip"]').tooltip();

            // boton para generar consulta al controlador reportes funcion rpt_cargos
            $('#btn-consultar').click(function(e) {
                // Aquí puedes agregar la lógica para generar el reporte
                e.preventDefault();
                window.location.href = '{{ url('reporte-cargos') }}?desde=' + $('#desde').val() +
                    '&hasta=' + $('#hasta').val();
            });

            // boton para limpiar los filtros
            $('#btn-limpiar').click(function(e) {
                e.preventDefault();
                // limpiar filtros de los input
                $('#desde').val('');
                $('#hasta').val('');
                window.location.href = '{{ url('reporte-cargos') }}';
            });

            // boton para generar la exportación a pdf del reporte
            $('#btn-exportar').click(function(e) {
                // Aquí puedes agregar la lógica para exportar el reporte
                e.preventDefault();
                window.open('{{ url('reporte-cargos') }}?desde=' + $('#desde').val() +
                    '&hasta=' + $('#hasta').val() + '&exportar=pdf', '_blank');
            });

            // boton para generar la exportación a excel del reporte
            $('#btn-exportar-excel').click(function(e) {
                e.preventDefault();
                window.open('{{ url('reporte-cargos') }}?desde=' + $('#desde').val() +
                    '&hasta=' + $('#hasta').val() + '&exportar=excel', '_blank');
            });
        });
    </script>
@endpush
