<div class="card-body p-0">
    <div class="table-responsive">
        <table class="table table-hover" id="marcas-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Descripción</th>
                    <th class="text-center">Estado</th>
                    <th class="text-center" colspan="3">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($marcas as $marca)
                    <tr>
                        <td>{{ $marca->id_marca }}</td>
                        <td>{{ $marca->descripcion }}</td>

                        {{-- 1. ESTADO VISUAL --}}
                        <td class="text-center">
                            @if($marca->estado)
                                <span class="badge badge-success">Activo</span>
                            @else
                                <span class="badge badge-danger">Inactivo</span>
                            @endif
                        </td>

                        <td class="text-center" style="width: 150px">
                            {!! Form::open(['route' => ['marcas.destroy', $marca->id_marca], 'method' => 'delete']) !!}
                            <div class='btn-group'>
                                
                                {{-- 2. EDITAR BLINDADO --}}
                                @can('marcas edit')
                                    @if($marca->estado)
                                        <a href="{{ route('marcas.edit', [$marca->id_marca]) }}" class='btn btn-default btn-xs' title="Editar">
                                            <i class="far fa-edit"></i>
                                        </a>
                                    @else
                                        <button type="button" class="btn btn-default btn-xs" disabled title="Debe activar la marca para editar">
                                            <i class="far fa-edit text-muted"></i>
                                        </button>
                                    @endif
                                @endcan

                                {{-- 3. HISTORIAL --}}
                                <button type="button" class="btn btn-info btn-xs btn-historial"
                                    data-id="{{ $marca->id_marca }}" 
                                    data-tabla="marcas" 
                                    title="Ver Historial">
                                    <i class="fas fa-history"></i>
                                </button>

                                {{-- 4. TOGGLE ACTIVAR/INACTIVAR --}}
                                @can('marcas destroy')
                                    @if ($marca->estado)
                                        {!! Form::button('<i class="fas fa-ban"></i>', [
                                            'type' => 'submit',
                                            'class' => 'btn btn-danger btn-xs alert-delete',
                                            'data-mensaje' => 'inactivar la marca ' . $marca->descripcion,
                                            'title' => 'Inactivar'
                                        ]) !!}
                                    @else
                                        {!! Form::button('<i class="fas fa-check"></i>', [
                                            'type' => 'submit',
                                            'class' => 'btn btn-success btn-xs alert-delete',
                                            'data-mensaje' => 'activar la marca ' . $marca->descripcion,
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
            {!! $marcas->links() !!}
        </div>
    </div>
</div>