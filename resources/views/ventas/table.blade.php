<div class="p-0 card-body">
    <div class="table-responsive">
        <table class="table" id="ventas-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>CI/RUC</th>
                    <th>Cliente</th>
                    <th>Fecha Venta</th>
                    <th>Factura Nro</th>
                    <th>Condición Venta</th>
                    <th>Total</th>
                    <th>Usuario</th>
                    <th>Estado</th>
                    <th colspan="3">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($ventas as $venta)
                    <tr>
                        <td>{{ $venta->id_venta }}</td>
                        <td>{{ $venta->clie_ci }}</td>
                        <td>{{ $venta->cliente }}</td>
                        <td>{{ \Carbon\Carbon::parse($venta->fecha_venta)->format('d/m/Y') }}</td>
                        <td>{{ $venta->factura_nro }}</td>
                        <td>{{ $venta->condicion_venta }}</td>
                        <td class="text-right">{{ number_format($venta->total, 0, ',', '.') }}</td>
                        <td>{{ $venta->usuario }}</td>
                        <td>
                            <span class="badge bg-{{ $venta->estado == 'COMPLETADO' ? 'info' : ($venta->estado == 'PAGADO' ? 'success' : 'danger') }}">
                                {{ $venta->estado }}
                            </span>
                        </td>
                        <td style="width: 120px">
                                    {!! Form::open(['route' => ['ventas.anular', $venta->id_venta], 'method' => 'post', 'class' => 'd-inline']) !!}
                            <div class='btn-group'>
                                <!-- Botón Cobros: visible si no está anulada ni pagada -->
                                @if($venta->estado !== 'ANULADO' && $venta->estado !== 'PAGADO')
                                    <a href="{{ route('cobros.index', ["id_venta" => $venta->id_venta]) }}" class='btn btn-warning btn-xs' title="Cobrar">
                                        <i class="far fa-money-bill-alt"></i>
                                    </a>
                                @endif

                                <!-- Botón Ver -->
                                <a href="{{ route('ventas.show', [$venta->id_venta]) }}" class='btn btn-default btn-xs' title="Ver">
                                    <i class="far fa-eye"></i>
                                </a>

                                <!-- Botón Imprimir: visible si no está anulada -->
                                @if($venta->estado !== 'ANULADO')
                                    <a href="{{ route('ventas.factura', $venta->id_venta) }}"
                                        class='btn btn-success btn-xs' title="Imprimir Factura">
                                        <i class="fas fa-print"></i>
                                    </a>
                                @endif
                                
                                <!-- Botón Editar: visible si no está anulada ni pagada -->
                                @if ($venta->estado !== 'ANULADO' && $venta->estado !== 'PAGADO')
                                    <a href="{{ route('ventas.edit', [$venta->id_venta]) }}"
                                        class='btn btn-info btn-xs' title="Editar">
                                        <i class="far fa-edit"></i>
                                    </a>
                                @endif

                                <!-- Botón Anular: visible si no está anulada -->
                                @if ($venta->estado !== 'ANULADO')
                                    {{-- ** CORRECCIÓN APLICADA AQUÍ ** --}}
                                    {{-- Se cambia el método a 'post' y la ruta a 'ventas.anular' --}}
                                        {!! Form::button('<i class="far fa-trash-alt"></i>', [
                                            'type' => 'submit',
                                            'class' => 'btn btn-danger btn-xs',
                                            'title' => 'Anular Venta',
                                            'data-mensaje' => 'la venta nro: '. $venta->id_venta
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
            @include('adminlte-templates::common.paginate', ['records' => $ventas])
        </div>
    </div>
</div>
