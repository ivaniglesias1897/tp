<div class="card-body p-0">
    <div class="table-responsive">
        <table class="table table-hover" id="cargos-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Descripción</th>
                    <th class="text-center">Estado</th>
                    <th class="text-center" style="width: 150px">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($cargos as $cargo)
                    <tr>
                        <td>{{ $cargo->id_cargo }}</td>
                        <td>{{ $cargo->descripcion }}</td>
                        
                        {{-- 1. COLUMNA ESTADO (Visual) --}}
                        <td class="text-center">
                            @if($cargo->estado)
                                <span class="badge badge-success">Activo</span>
                            @else
                                <span class="badge badge-danger">Inactivo</span>
                            @endif
                        </td>

                        <td class="text-center">
                            {{-- Formulario para Activar/Desactivar apuntando a destroy --}}
                            {!! Form::open(['route' => ['cargos.destroy', $cargo->id_cargo], 'method' => 'delete']) !!}
                            <div class='btn-group'>
                                
                                {{-- 2. BOTÓN EDITAR (Blindado) --}}
                                @can('cargos edit')
                                    @if($cargo->estado)
                                        <a href="{{ route('cargos.edit', [$cargo->id_cargo]) }}" class='btn btn-default btn-xs' title="Editar Cargo">
                                            <i class="far fa-edit"></i>
                                        </a>
                                    @else
                                        {{-- Botón gris deshabilitado si está inactivo --}}
                                        <button type="button" class="btn btn-default btn-xs" disabled title="Debe activar el cargo para editarlo">
                                            <i class="far fa-edit text-muted"></i>
                                        </button>
                                    @endif
                                @endcan

                                {{-- 3. BOTÓN HISTORIAL (Preparado para cuando agreguemos el modal) --}}
                                <button type="button" class="btn btn-info btn-xs btn-historial"
                                    data-id="{{ $cargo->id_cargo }}" 
                                    data-tabla="cargos" 
                                    title="Ver Historial de Cambios">
                                    <i class="fas fa-history"></i>
                                </button>

                                {{-- 4. BOTÓN ACTIVAR / INACTIVAR (Lógica Toggle) --}}
                                @can('cargos destroy')
                                    @if ($cargo->estado)
                                        {{-- Si está ACTIVO, mostramos botón ROJO para BLOQUEAR --}}
                                        {!! Form::button('<i class="fas fa-ban"></i>', [
                                            'type' => 'submit',
                                            'class' => 'btn btn-danger btn-xs alert-delete',
                                            'data-mensaje' => 'inactivar el cargo ' . $cargo->descripcion,
                                            'title' => 'Inactivar Cargo'
                                        ]) !!}
                                    @else
                                        {{-- Si está INACTIVO, mostramos botón VERDE para ACTIVAR --}}
                                        {!! Form::button('<i class="fas fa-check"></i>', [
                                            'type' => 'submit',
                                            'class' => 'btn btn-success btn-xs alert-delete',
                                            'data-mensaje' => 'activar el cargo ' . $cargo->descripcion,
                                            'title' => 'Activar Cargo'
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
            {!! $cargos->links() !!}
        </div>
    </div>
</div>