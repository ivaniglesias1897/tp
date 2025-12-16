@extends('layouts.app')

@section('content')
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Listado de Usuarios</h1>
                </div>
                <div class="col-sm-6">
                    @can('users create')
                        <a class="btn btn-primary float-right"
                        href="{{ route('users.create') }}">
                        <i class="fas fa-plus"></i>
                            Nuevo Usuario
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

        <!-- agregar la clase tabla-container para mostrar los valores filtrados de table-->
        <div class="card tabla-container">
            @include('users.table')
        </div>
    </div>

    {{-- 1. EL MODAL DE HISTORIAL (Estructura idéntica a Clientes/Proveedores) --}}
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
                    {{-- Este div recibirá la respuesta del controlador (auditoria_timeline.blade.php) --}}
                    <div id="contenido-historial"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                </div>
            </div>
        </div>
    </div>
@endsection

{{-- 2. EL SCRIPT JAVASCRIPT para la funcionalidad AJAX --}}
@push('scripts')
<script>
    $(document).ready(function() {
        
        // Delegación de eventos: escucha cualquier botón .btn-historial dentro del body
        // Crucial para tablas que se recargan vía AJAX (buscador, paginación).
        $('body').on('click', '.btn-historial', function() {
            
            // Obtenemos los datos del botón
            var id = $(this).data('id');
            var tabla = $(this).data('tabla'); // Debería ser 'users'
            
            // A. Abrimos el modal y mostramos spinner de carga
            $('#modalHistorial').modal('show');
            $('#contenido-historial').html('<div class="text-center py-5"><div class="spinner-border text-primary"></div><p class="mt-2">Cargando historial...</p></div>');

            // B. Llamamos al controlador de auditoría.
            // NOTA: La tabla de usuarios usa 'users' y la clave primaria es 'id', lo cual ya está mapeado.
            $.get('/auditoria/historial/' + tabla + '/' + id, function(data) {
                $('#contenido-historial').html(data); // Inyecta el HTML del timeline
            }).fail(function() {
                $('#contenido-historial').html('<div class="alert alert-danger"><i class="fas fa-exclamation-triangle"></i> Error: No se pudo cargar el historial de usuario.</div>');
            });
        });
    });
</script>
@endpush