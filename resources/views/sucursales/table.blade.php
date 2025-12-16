<div class="card-body p-0">
    <div class="table-responsive">
        <table class="table table-hover" id="sucursales-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Descripción</th>
                    <th>Dirección</th>
                    <th>Teléfono</th>
                    <th>Ciudad</th>
                    {{-- NUEVA COLUMNA ESTADO --}}
                    <th class="text-center">Estado</th>
                    <th class="text-center" colspan="3">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($sucursales as $sucursal)
                    <tr>
                        <td>{{ $sucursal->id_sucursal }}</td>
                        <td>{{ $sucursal->descripcion }}</td>
                        <td>{{ $sucursal->direccion }}</td>
                        <td>{{ $sucursal->telefono }}</td>
                        <td>{{ $sucursal->ciudad }}</td>
                        
                        {{-- 1. ESTADO VISUAL --}}
                        <td class="text-center">
                            @if($sucursal->estado)
                                <span class="badge badge-success">Activo</span>
                            @else
                                <span class="badge badge-danger">Inactivo</span>
                            @endif
                        </td>

                        <td class="text-center" style="width: 150px">
                            {!! Form::open(['route' => ['sucursales.destroy', $sucursal->id_sucursal], 'method' => 'delete']) !!}
                            <div class='btn-group'>
                                
                                {{-- 2. EDITAR BLINDADO --}}
                                @can('sucursales edit')
                                    @if($sucursal->estado)
                                        <a href="{{ route('sucursales.edit', [$sucursal->id_sucursal]) }}"
                                           class='btn btn-default btn-xs' title="Editar">
                                            <i class="far fa-edit"></i>
                                        </a>
                                    @else
                                        <button type="button" class="btn btn-default btn-xs" disabled title="Debe activar la sucursal para editar">
                                            <i class="far fa-edit text-muted"></i>
                                        </button>
                                    @endif
                                @endcan

                                {{-- 3. BOTÓN HISTORIAL --}}
                                <button type="button" class="btn btn-info btn-xs btn-historial"
                                    data-id="{{ $sucursal->id_sucursal }}" 
                                    data-tabla="sucursales" 
                                    title="Ver Historial">
                                    <i class="fas fa-history"></i>
                                </button>

                                {{-- 4. TOGGLE ACTIVAR/INACTIVAR --}}
                                @can('sucursales destroy')
                                    @if ($sucursal->estado)
                                        {!! Form::button('<i class="fas fa-ban"></i>', [
                                            'type' => 'submit',
                                            'class' => 'btn btn-danger btn-xs alert-delete',
                                            'data-mensaje' => 'inactivar la sucursal ' . $sucursal->descripcion,
                                            'title' => 'Inactivar'
                                        ]) !!}
                                    @else
                                        {!! Form::button('<i class="fas fa-check"></i>', [
                                            'type' => 'submit',
                                            'class' => 'btn btn-success btn-xs alert-delete',
                                            'data-mensaje' => 'activar la sucursal ' . $sucursal->descripcion,
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
                        <td colspan="7" class="text-center text-muted">No se encontraron sucursales registradas.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="card-footer clearfix">
        <div class="float-right">
            {!! $sucursales->links() !!}
        </div>
    </div>
</div>