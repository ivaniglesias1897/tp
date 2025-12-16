@extends('layouts.app')

@section('content')
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Sucursales</h1>
                </div>
                <div class="col-sm-6">
                    @can('sucursales create')
                        <a class="btn btn-primary float-right" href="{{ route('sucursales.create') }}">
                            <i class="fas fa-plus-circle"></i>
                            Nueva Sucursal
                        </a>
                    @endcan
                </div>
            </div>
        </div>
    </section>

    <div class="content px-3">
        @include('sweetalert::alert')

        <div class="clearfix">
            @includeIf('layouts.buscador', ['url' => url()->current()])
        </div>

        <div class="card tabla-container" id="sucursales-table-container">
            @include('sucursales.table')
        </div>
    </div>

    {{-- === MODAL DE HISTORIAL (Estandarizado) === --}}
    <div class="modal fade" id="modalHistorial" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header bg-info">
                    <h5 class="modal-title text-white">
                        <i class="fas fa-history mr-2"></i> Historial de Cambios
                    </h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body bg-light" style="max-height: 70vh; overflow-y: auto;">
                    <div id="contenido-historial"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        
        // --- 1. FUNCIÓN AJAX CENTRALIZADA ---
        function loadTable(url) {
            $.ajax({
                url: url,
                type: 'GET',
                headers: { 'X-Requested-With': 'XMLHttpRequest' },
                success: function(data) {
                    $('#sucursales-table-container').html(data); 
                },
                error: function(xhr) {
                    console.error("Error al cargar la tabla:", xhr);
                }
            });
        }
        
        // --- 2. PAGINACIÓN ---
        $(document).on('click', '#sucursales-table-container .pagination a', function(e) {
            e.preventDefault();
            loadTable($(this).attr('href'));
        });
        
        // --- 3. BÚSQUEDA ---
        $('form[method="GET"]').on('submit', function(e) {
             if ($(this).find('input[name="buscar"]').length > 0 && 
                 $(this).find('button[type="submit"]').html().includes('<i class="fa fa-search"></i>')) {
                 
                 e.preventDefault();
                 var buscar = $(this).find('input[name="buscar"]').val();
                 var url = '{{ route('sucursales.index') }}' + '?buscar=' + buscar;
                 loadTable(url);
             }
        });

        // --- 4. LÓGICA DE HISTORIAL ---
        $('body').on('click', '.btn-historial', function() {
            var id = $(this).data('id');
            var tabla = $(this).data('tabla'); // 'sucursales'
            
            $('#modalHistorial').modal('show');
            $('#contenido-historial').html('<div class="text-center py-5"><div class="spinner-border text-primary"></div><p class="mt-2">Cargando historial...</p></div>');

            $.get('/auditoria/historial/' + tabla + '/' + id, function(data) {
                $('#contenido-historial').html(data);
            }).fail(function() {
                $('#contenido-historial').html('<div class="alert alert-danger">No se pudo cargar el historial.</div>');
            });
        });
        
    });
</script>
@endpush