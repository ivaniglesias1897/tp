<div class="p-0 card-body">
    <div class="table-responsive">
        <table class="table" id="pedidos-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Ci/Ruc</th>
                    <th>Cliente</th>
                    <th>Fecha Pedido</th>
                    <th>Total Pedido</th>
                    <th>Usuario</th>
                    <th>Estado</th>
                    <th colspan="3">Action</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($pedidos as $pedido)
                    <tr>
                        <td>{{ $pedido->id_pedido }}</td>
                        <td>{{ $pedido->clie_ci }}</td>
                        <td>{{ $pedido->cliente }}</td>
                        <td>{{ \Carbon\Carbon::parse($pedido->fecha_pedido)->format('d/m/Y') }}</td>
                        <td>{{ number_format($pedido->total_pedido, 0, ',', '.') }}</td>
                        <td>{{ $pedido->usuario }}</td>
                        <td>
                            <span class="badge bg-{{ $pedido->estado == 'COMPLETADO' ? 'success' : 'danger' }}">
                                {{ $pedido->estado }}
                            </span>
                        </td>
                        <td style="width: 120px">
                            {!! Form::open(['route' => ['pedidos.destroy', $pedido->id_pedido], 'method' => 'delete']) !!}
                            <div class='btn-group'>
                                <a href="{{ route('pedidos.show', [$pedido->id_pedido]) }}" class='btn btn-default btn-xs'>
                                    <i class="far fa-eye"></i>
                                </a>
                                <!-- validacion para mostrar los botones de borrar y editar si la pedido es cancelada -->
                                @if($pedido->estado != 'CANCELADO')
                                    <a href="{{ route('pedidos.edit', [$pedido->id_pedido]) }}" class='btn btn-default btn-xs'>
                                        <i class="far fa-edit"></i>
                                    </a>
                                    {!! Form::button('<i class="far fa-trash-alt"></i>', [
                                        'type' => 'submit',
                                        'class' => 'btn btn-danger btn-xs alert-delete',
                                        'data-mensaje' => $pedido->id_pedido,
                                    ]) !!}
                                @endif
                            </div>
                            {!! Form::close() !!}
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="clearfix card-footer">
        <div class="float-right">
            {{-- @include('adminlte-templates::common.paginate', ['records' => $pedido]) --}}
        </div>
    </div>
</div>