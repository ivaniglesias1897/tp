<div class="card-body p-0">
    <div class="table-responsive">
        <table class="table" id="compras-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Fecha</th>
                    <th>Condición</th>
                    <th>Factura</th>
                    <th>Proveedor</th>
                    <th>Usuario</th>
                    <th>Estado</th>
                    <th class="text-right">Total</th>
                    <th style="width: 120px;">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse($compras as $compra)
                    <tr>
                        <td>{{ $compra->id_compra }}</td>
                        <td>{{ \Carbon\Carbon::parse($compra->fecha_compra)->format('d/m/Y') }}</td>
                        <td>{{ $compra->condicion_compra }}</td>
                        <td>{{ $compra->factura }}</td>
                        <td>{{ $compra->proveedor }}</td>
                        <td>{{ $compra->usuario }}</td>
                        <td>
                            @if ($compra->estado == 'ANULADO')
                                <span class="badge badge-danger">{{ $compra->estado }}</span>
                            @else
                                <span class="badge badge-success">{{ $compra->estado ?? 'RECIBIDO' }}</span>
                            @endif
                        </td>
                        <td class="text-right">{{ number_format($compra->total ?? 0, 0, ',', '.') }}</td>
                        <td>
                            {!! Form::open([
                                        'route' => ['compras.destroy', $compra->id_compra],
                                        'method' => 'delete',
                                        'class' => 'd-inline',
                                    ]) !!}
                            <div class="btn-group">
                                <a href="{{ route('compras.show', $compra->id_compra) }}" class="btn btn-default btn-xs"
                                    title="Ver"><i class="far fa-eye"></i></a>

                                {{-- Los botones de Editar y Anular solo aparecen si la compra NO está anulada --}}
                                @if ($compra->estado != 'ANULADO')
                                    <a href="{{ route('compras.edit', $compra->id_compra) }}"
                                        class="btn btn-info btn-xs" title="Editar"><i class="far fa-edit"></i></a>
                                    
                                    {!! Form::button('<i class="far fa-trash-alt"></i>', [
                                        'type' => 'submit',
                                        'class' => 'btn btn-danger btn-xs alert-delete',
                                        'title' => 'Anular Compra',
                                        'data-mensaje' => 'la compra Nro. ' . $compra->id_compra,
                                    ]) !!}
                                @endif
                            </div>
                            {!! Form::close() !!}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" class="text-center text-muted">No hay compras registradas.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="card-footer clearfix">
        <div class="float-right">
            {{-- Se asegura de que la paginación funcione incluso con filtros de búsqueda --}}
            @if ($compras instanceof \Illuminate\Pagination\LengthAwarePaginator)
                {{ $compras->withQueryString()->links() }}
            @endif
        </div>
    </div>
</div>
