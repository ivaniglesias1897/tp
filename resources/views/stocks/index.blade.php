@extends('layouts.app')

@section('content')
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Stocks</h1>
                </div>
                <div class="col-sm-6">
                    
                    {{-- NUEVO BOTÓN DE AYUDA / MANUAL --}}
                    {{-- Se dirige al archivo PDF ubicado en public/manuales/manual_stock.pdf --}}
                    <a class="btn btn-default float-right mr-2" href="{{ asset('manuales/manual_stock.pdf') }}" target="_blank"
                        title="Ayuda / Manual de Usuario" data-toggle="tooltip">
                        <i class="fas fa-question-circle"></i> Ayuda
                        </a>

                    {{-- Botón para acceder al nuevo formulario de ajuste --}}
                    @can('stocks create')
                        <a class="btn btn-primary float-right" href="{{ route('stocks.create') }}">
                            <i class="fas fa-plus"></i> Ajustar Stock
                            </a>
                    @endcan

                    
                </div>
                </div>
            </div>
    </section>

    <div class="content px-3">
        @include('sweetalert::alert')
        @include('adminlte-templates::common.errors')
        
        <div class="card card-default">
            <div class="card-body">
                {{-- Usamos un ID para el formulario para controlarlo con JS --}}
                <form method="GET" action="{{ route('stocks.index') }}" id="filter-form">
                    <div class="row">

                        <div class="form-group col-md-4">
                            {!! Form::label('buscar', 'Buscar (Producto o Sucursal):') !!}
                            {!! Form::text('buscar', request('buscar'), [
                                'class' => 'form-control',
                                'placeholder' => 'Escriba aquí...',
                                'id' => 'stock-search-input',
                            ]) !!}
                            </div>
                        
                        <!-- Filtro Sucursal (Nuevo) -->
                        <div class="form-group col-md-3">
                            {!! Form::label('id_sucursal', 'Filtrar por Sucursal:') !!}
                            {{-- Corregido: Si $sucursales existe, se usa, si no, array() para evitar error --}}
                            {!! Form::select('id_sucursal', $sucursales ?? [], request('id_sucursal'), [
                                'class' => 'form-control select2',
                                'placeholder' => 'Todas las sucursales',
                                'id' => 'id_sucursal',
                            ]) !!}
                            </div>

                        <!-- Filtro Producto (Nuevo) -->
                        <div class="form-group col-md-3">
                            {!! Form::label('id_producto', 'Filtrar por Producto:') !!}
                            {{-- Corregido: Si $productos existe, se usa, si no, array() para evitar error --}}
                            {!! Form::select('id_producto', $productos ?? [], request('id_producto'), [
                                'class' => 'form-control select2',
                                'placeholder' => 'Todos los productos',
                                'id' => 'id_producto',
                            ]) !!}
                            </div>

                        <!-- Botones -->
                        <div class="form-group col-md-2 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary" data-toggle="tooltip"
                                data-placement="top" title="Filtrar">
                                <i class="fas fa-search"></i>
                                </button>
                            {{-- CAMBIO: Convertimos <a> en <button> y añadimos un ID --}}
                            <button type="button" id="btn-limpiar-filtros" class="btn btn-default ml-2"
                                data-toggle="tooltip" data-placement="top" title="Limpiar filtros">
                                <i class="fas fa-sync-alt"></i>
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        <!-- Fin Formulario de Filtros -->

        {{-- Este es el contenedor que se actualizará por AJAX --}}
        <div class="card tabla-container">
            @include('stocks.table')
            </div>
    </div>
@endsection

@push('scripts')
    <script>
        $(document).ready(function() {
                    // 1. Inicializar los tooltips
                    $('[data-toggle="tooltip"]').tooltip();

                    // 2. Lógica de búsqueda AJAX
                    let searchTimer; // Variable para el temporizador (debounce)

                    // Función principal para la búsqueda AJAX
                    function fetchStockData(urlOverride) {
                        let url;
                        if (urlOverride) {
                            url = urlOverride;
                        } else {
                            // Obtenemos todos los valores de los filtros
                            let buscar = $('#stock-search-input').val();
                            let sucursal = $('#id_sucursal').val();
                            let producto = $('#id_producto').val();

                            // Construimos la URL con los parámetros
                            url = new URL('{{ route('stocks.index') }}');
                            url.searchParams.set('buscar', buscar || '');
                            url.searchParams.set('id_sucursal', sucursal || '');
                            url.searchParams.set('id_producto', producto || '');
                        }

                        // Hacemos la petición Fetch
                        fetch(url.toString(), {
                                method: 'GET',
                                headers: {
                                    'X-Requested-With': 'XMLHttpRequest' // Esencial para que el controlador detecte AJAX
                                }
                            })
                            .then(response => response.text())
                            .then(html => {
                                // Reemplazamos solo el contenido de la tabla
                                $('.tabla-container').html(html);
                                // Re-inicializamos tooltips en la nueva tabla (si los hubiera)
                                $('[data-toggle="tooltip"]').tooltip();
                            })
                            .catch(error => console.error('Error en la búsqueda AJAX:', error));
                    }

                    // Disparar AJAX al teclear en el buscador (con 500ms de espera)
                    $('#stock-search-input').on('keyup', function() {
                        clearTimeout(searchTimer); // Limpiamos el temporizador anterior
                        searchTimer = setTimeout(function() {
                            fetchStockData();
                        }, 500); // Esperamos 500ms antes de buscar
                    });

                    // Disparar AJAX al cambiar un filtro <select>
                    $('#id_sucursal, #id_producto').on('change', function() {
                        fetchStockData(); // Aquí lo hacemos al instante
                    });

                    // Evitar que el formulario se envíe de forma tradicional (al presionar Enter o el botón)
                    $('#filter-form').on('submit', function(e) {
                        e.preventDefault(); // Detenemos el envío normal
                        fetchStockData(); // Y en su lugar, ejecutamos el AJAX
                    });

                    // Manejar clic en el botón "Limpiar"
                    $('#btn-limpiar-filtros').on('click', function() {
                        // 1. Limpiar los campos de filtro
                        $('#stock-search-input').val('');
                        $('#id_sucursal').val(null).trigger('change.select2'); // Limpiar select2
                        $('#id_producto').val(null).trigger('change.select2'); // Limpiar select2

                        // 2. Recargar la tabla con los filtros limpios
                        fetchStockData();

                        // 3. Poner el focus en el buscador (Tu petición)
                        $('#stock-search-input').focus();
                    });

                    // Manejar paginación AJAX
                    $(document).on('click', '.pagination a', function(e) {
                        e.preventDefault();
                        let url = $(this).attr('href');
                        fetchStockData(url); // Usar la URL de paginación });
                    });
    </script>
@endpush
