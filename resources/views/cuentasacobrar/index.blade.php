@extends('layouts.app')

@section('content')
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Cuentas a Cobrar</h1>
                </div>
            </div>
        </div>
    </section>

    <div class="content px-3">

        @include('sweetalert::alert')

        <div class="clearfix">
           @includeIf('layouts.buscador', ['url' => url()->current()])
        </div>

        <!-- agregar la clase tabla-container para mostrar los valores filtrados de table-->
        <div class="card tabla-container">
            @include('cuentasacobrar.table')
        </div>
    </div>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <script>
        $(document).ready(function() {
            // limpiar filtros con jquery
            $('#btn-limpiar').click(function() {
                $('#sucursales').val('').trigger('change');
                $('#productos').val('').trigger('change');
                $('.buscar').val('');
                $('#form-busqueda').submit();
            });
        });
    </script>
@endsection