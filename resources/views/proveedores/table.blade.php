<div class="card-body p-0">
    <div class="table-responsive">
        <table class="table table-hover" id="proveedores-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Descripción</th>
                    <th>Dirección</th>
                    <th>Teléfono</th>
                    <th class="text-center">Estado</th>
                    <th class="text-center" colspan="3">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($proveedores as $proveedor)
                    <tr>
                        <td>{{ $proveedor->id_proveedor }}</td>
                        <td>{{ $proveedor->descripcion }}</td>
                        <td>{{ $proveedor->direccion }}</td>
                        <td>{{ $proveedor->telefono }}</td>
                        
                        {{-- 1. ESTADO VISUAL --}}
                        <td class="text-center">
                            @if($proveedor->estado)
                                <span class="badge badge-success">Activo</span>
                            @else
                                <span class="badge badge-danger">Inactivo</span>
                            @endif
                        </td>

                        <td class="text-center" style="width: 150px">
                            {!! Form::open(['route' => ['proveedores.destroy', $proveedor->id_proveedor], 'method' => 'delete']) !!}
                            <div class='btn-group'>
                                
                                {{-- 2. EDITAR BLINDADO --}}
                                @can('proveedores edit')
                                    @if($proveedor->estado)
                                        <a href="{{ route('proveedores.edit', [$proveedor->id_proveedor]) }}"
                                           class='btn btn-default btn-xs' title="Editar">
                                            <i class="far fa-edit"></i>
                                        </a>
                                    @else
                                        <button type="button" class="btn btn-default btn-xs" disabled title="Debe activar el proveedor para editar">
                                            <i class="far fa-edit text-muted"></i>
                                        </button>
                                    @endif
                                @endcan

                                {{-- 3. BOTÓN HISTORIAL --}}
                                <button type="button" class="btn btn-info btn-xs btn-historial"
                                    data-id="{{ $proveedor->id_proveedor }}" 
                                    data-tabla="proveedores" 
                                    title="Ver Historial">
                                    <i class="fas fa-history"></i>
                                </button>

                                {{-- 4. TOGGLE ACTIVAR/INACTIVAR --}}
                                @can('proveedores destroy')
                                    @if ($proveedor->estado)
                                        {!! Form::button('<i class="fas fa-ban"></i>', [
                                            'type' => 'submit',
                                            'class' => 'btn btn-danger btn-xs alert-delete',
                                            'data-mensaje' => 'inactivar al proveedor ' . $proveedor->descripcion,
                                            'title' => 'Inactivar'
                                        ]) !!}
                                    @else
                                        {!! Form::button('<i class="fas fa-check"></i>', [
                                            'type' => 'submit',
                                            'class' => 'btn btn-success btn-xs alert-delete',
                                            'data-mensaje' => 'activar al proveedor ' . $proveedor->descripcion,
                                            'title' => 'Activar'
                                        ]) !!}
                                    @endif
                                @endcan

                            </div>
                            {!! Form::close() !!}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center text-muted">No se encontraron proveedores registrados.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="card-footer clearfix">
        <div class="float-right">
            {!! $proveedores->links() !!}
        </div>
    </div>
</div>