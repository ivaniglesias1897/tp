<div class="card-body p-0">
    <div class="table-responsive">
        <table class="table table-hover" id="cajas-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Descripción</th>
                    <th>Sucursal</th>
                    <th>Punto Exp.</th>
                    <th>Última Factura</th>
                    {{-- NUEVA COLUMNA ESTADO --}}
                    <th class="text-center">Estado</th>
                    <th class="text-center" colspan="3">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($cajas as $caja)
                    <tr>
                        <td>{{ $caja->id_caja }}</td>
                        <td>{{ $caja->descripcion }}</td>
                        <td>{{ $caja->sucursal }}</td>
                        <td>{{ $caja->punto_expedicion }}</td>
                        <td>{{ number_format($caja->ultima_factura_impresa, 0, ',', '.') }}</td>

                        {{-- 1. ESTADO VISUAL --}}
                        <td class="text-center">
                            @if($caja->estado)
                                <span class="badge badge-success">Activo</span>
                            @else
                                <span class="badge badge-danger">Inactivo</span>
                            @endif
                        </td>

                        <td class="text-center" style="width: 150px">
                            {!! Form::open(['route' => ['cajas.destroy', $caja->id_caja], 'method' => 'delete']) !!}
                            <div class='btn-group'>
                                
                                {{-- 2. EDITAR BLINDADO --}}
                                @can('cajas edit')
                                    @if($caja->estado)
                                        <a href="{{ route('cajas.edit', [$caja->id_caja]) }}" class='btn btn-default btn-xs' title="Editar">
                                            <i class="far fa-edit"></i>
                                        </a>
                                    @else
                                        <button type="button" class="btn btn-default btn-xs" disabled title="Debe activar la caja para editar">
                                            <i class="far fa-edit text-muted"></i>
                                        </button>
                                    @endif
                                @endcan

                                {{-- 3. BOTÓN HISTORIAL --}}
                                <button type="button" class="btn btn-info btn-xs btn-historial"
                                    data-id="{{ $caja->id_caja }}" 
                                    data-tabla="cajas" 
                                    title="Ver Historial">
                                    <i class="fas fa-history"></i>
                                </button>

                                {{-- 4. TOGGLE ACTIVAR/INACTIVAR --}}
                                @can('cajas destroy')
                                    @if ($caja->estado)
                                        {!! Form::button('<i class="fas fa-ban"></i>', [
                                            'type' => 'submit',
                                            'class' => 'btn btn-danger btn-xs alert-delete',
                                            'data-mensaje' => 'inactivar la caja ' . $caja->descripcion,
                                            'title' => 'Inactivar'
                                        ]) !!}
                                    @else
                                        {!! Form::button('<i class="fas fa-check"></i>', [
                                            'type' => 'submit',
                                            'class' => 'btn btn-success btn-xs alert-delete',
                                            'data-mensaje' => 'activar la caja ' . $caja->descripcion,
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
                        <td colspan="7" class="text-center text-muted">No se encontraron cajas registradas.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="card-footer clearfix">
        <div class="float-right">
            {!! $cajas->links() !!}
        </div>
    </div>
</div>