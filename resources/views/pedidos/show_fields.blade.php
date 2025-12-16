<!-- Id Cliente Field -->
<div class="col-sm-4">
    {!! Form::label('id_cliente', 'Cliente:') !!}
    <p>{{ $pedido->cliente }}</p>
</div>

<!-- Total Field -->
<div class="col-sm-4">
    {!! Form::label('total', 'Total:') !!}
    <p>{{ number_format($pedido->total_pedido, 0, ',', '.') }}</p>
</div>

<!-- Fecha Pedido Field -->
<div class="col-sm-4">
    {!! Form::label('fecha_pedido', 'Fecha Pedido:') !!}
    <p>{{ \Carbon\Carbon::parse($pedido->fecha_pedido)->format('d/m/Y') }}</p>
</div>

<!-- User Id Field -->
<div class="col-sm-4">
    {!! Form::label('user_id', 'Vendedor:') !!}
    <p>{{ $pedido->usuario }}</p>
</div>

<!-- Detalle Pedido -->
<div class="col-sm-12">
    <h4>Detalle Pedido</h4>
    <table class="table table-bordered table-striped table-hover">
        <thead>
            <tr>
                <th>Código Producto</th>
                <th>Producto</th>
                <th>Cantidad</th>
                <th>Precio</th>
                <th>Subtotal</th>
            </tr>
        </thead>
        <tbody>
            @foreach($detalle_pedido as $detalle)
                <tr>
                    <td>{{ $detalle->id_producto }}</td>
                    <td>{{ $detalle->descripcion }}</td>
                    <td>{{ $detalle->cantidad }}</td>
                    <td>{{ number_format($detalle->precio_unitario, 0, ',', '.') }}</td>
                    <td>{{ number_format($detalle->precio_unitario * $detalle->cantidad, 0, ',', '.') }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>