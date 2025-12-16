<div class="card-body p-0">
    <div class="table-responsive">
        <table class="table table-hover" id="ciudades-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Ciudad</th>
                    <th>Departamento</th>
                    {{-- NUEVA COLUMNA ESTADO --}}
                    <th class="text-center">Estado</th>
                    <th class="text-center" colspan="3">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($ciudades as $ciudad)
                    <tr>
                        <td>{{ $ciudad->id_ciudad }}</td>
                        <td>{{ $ciudad->descripcion }}</td>
                        <td>{{ $ciudad->departamento }}</td>

                        {{-- 1. ESTADO VISUAL --}}
                        <td class="text-center">
                            @if($ciudad->estado)
                                <span class="badge badge-success">Activo</span>
                            @else
                                <span class="badge badge-danger">Inactivo</span>
                            @endif
                        </td>

                        <td class="text-center" style="width: 150px">
                            {!! Form::open(['route' => ['ciudades.destroy', $ciudad->id_ciudad], 'method' => 'delete']) !!}
                            <div class='btn-group'>
                                
                                {{-- 2. EDITAR BLINDADO --}}
                                @can('ciudades edit')
                                    @if($ciudad->estado)
                                        <a href="{{ route('ciudades.edit', [$ciudad->id_ciudad]) }}" class='btn btn-default btn-xs' title="Editar">
                                            <i class="far fa-edit"></i>
                                        </a>
                                    @else
                                        <button type="button" class="btn btn-default btn-xs" disabled title="Debe activar la ciudad para editarla">
                                            <i class="far fa-edit text-muted"></i>
                                        </button>
                                    @endif
                                @endcan

                                {{-- 3. BOTÓN HISTORIAL --}}
                                <button type="button" class="btn btn-info btn-xs btn-historial"
                                    data-id="{{ $ciudad->id_ciudad }}" 
                                    data-tabla="ciudades" 
                                    title="Ver Historial">
                                    <i class="fas fa-history"></i>
                                </button>

                                {{-- 4. TOGGLE ACTIVAR/INACTIVAR --}}
                                @can('ciudades destroy')
                                    @if ($ciudad->estado)
                                        {!! Form::button('<i class="fas fa-ban"></i>', [
                                            'type' => 'submit',
                                            'class' => 'btn btn-danger btn-xs alert-delete',
                                            'data-mensaje' => 'inactivar la ciudad ' . $ciudad->descripcion,
                                            'title' => 'Inactivar'
                                        ]) !!}
                                    @else
                                        {!! Form::button('<i class="fas fa-check"></i>', [
                                            'type' => 'submit',
                                            'class' => 'btn btn-success btn-xs alert-delete',
                                            'data-mensaje' => 'activar la ciudad ' . $ciudad->descripcion,
                                            'title' => 'Activar'
                                        ]) !!}
                                    @endif
                                @endcan

                            </div>
                            {!! Form::close() !!}
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="card-footer clearfix">
        <div class="float-right">
            {!! $ciudades->links() !!}
        </div>
    </div>
</div>