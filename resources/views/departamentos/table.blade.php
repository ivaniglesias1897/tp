<div class="card-body p-0">
    <div class="table-responsive">
        <table class="table table-hover" id="departamentos-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Descripción</th>
                    {{-- NUEVA COLUMNA ESTADO --}}
                    <th class="text-center">Estado</th>
                    <th class="text-center" colspan="3">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($departamentos as $departamento)
                    <tr>
                        <td>{{ $departamento->id_departamento }}</td>
                        <td>{{ $departamento->descripcion }}</td>
                        
                        {{-- 1. ESTADO VISUAL --}}
                        <td class="text-center">
                            @if($departamento->estado)
                                <span class="badge badge-success">Activo</span>
                            @else
                                <span class="badge badge-danger">Inactivo</span>
                            @endif
                        </td>

                        <td class="text-center" style="width: 150px">
                            {!! Form::open(['route' => ['departamentos.destroy', $departamento->id_departamento], 'method' => 'delete']) !!}
                            <div class='btn-group'>
                                
                                {{-- 2. BOTÓN EDITAR (Condicional) --}}
                                @can('departamentos edit')
                                    @if($departamento->estado)
                                        <a href="{{ route('departamentos.edit', [$departamento->id_departamento]) }}"
                                            class='btn btn-default btn-xs' title="Editar">
                                            <i class="far fa-edit"></i>
                                        </a>
                                    @else
                                        <button type="button" class="btn btn-default btn-xs" disabled title="Debe activar para editar">
                                            <i class="far fa-edit text-muted"></i>
                                        </button>
                                    @endif
                                @endcan

                                {{-- 3. BOTÓN HISTORIAL (Nuevo) --}}
                                <button type="button" class="btn btn-info btn-xs btn-historial"
                                    data-id="{{ $departamento->id_departamento }}" 
                                    {{-- ¡OJO! data-tabla debe coincidir con tu BD (plural) --}}
                                    data-tabla="departamentos" 
                                    title="Ver Historial">
                                    <i class="fas fa-history"></i>
                                </button>

                                {{-- 4. BOTÓN ACTIVAR / INACTIVAR (Lógica Toggle) --}}
                                @can('departamentos destroy')
                                    @if ($departamento->estado)
                                        {{-- Botón ROJO para INACTIVAR --}}
                                        {!! Form::button('<i class="fas fa-ban"></i>', [
                                            'type' => 'submit',
                                            'class' => 'btn btn-danger btn-xs alert-delete',
                                            'data-mensaje' => 'inactivar el departamento ' . $departamento->descripcion,
                                            'title' => 'Inactivar'
                                        ]) !!}
                                    @else
                                        {{-- Botón VERDE para ACTIVAR --}}
                                        {!! Form::button('<i class="fas fa-check"></i>', [
                                            'type' => 'submit',
                                            'class' => 'btn btn-success btn-xs alert-delete',
                                            'data-mensaje' => 'activar el departamento ' . $departamento->descripcion,
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
            {!! $departamentos->links() !!}
        </div>
    </div>
</div>