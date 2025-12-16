<!-- Id Producto Field -->
<div class="col-sm-12">
    {!! Form::label('id_producto', 'Id Producto:') !!}
    <p>{{ $stock->id_producto }}</p>
</div>

<!-- Id Sucursal Field -->
<div class="col-sm-12">
    {!! Form::label('id_sucursal', 'Id Sucursal:') !!}
    <p>{{ $stock->id_sucursal }}</p>
</div>

<!-- Cantidad Field -->
<div class="col-sm-12">
    {!! Form::label('cantidad', 'Cantidad:') !!}
    <p>{{ $stock->cantidad }}</p>
</div>
