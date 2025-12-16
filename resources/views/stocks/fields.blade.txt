<!-- Id Producto Field -->
<div class="form-group col-sm-6">
    {!! Form::label('id_producto', 'Id Producto:') !!}
    {!! Form::text('id_producto', null, ['class' => 'form-control']) !!}
</div>

<!-- Id Sucursal Field -->
<div class="form-group col-sm-6">
    {!! Form::label('id_sucursal', 'Id Sucursal:') !!}
    {!! Form::number('id_sucursal', null, ['class' => 'form-control']) !!}
</div>

<!-- Cantidad Field -->
<div class="form-group col-sm-6">
    {!! Form::label('cantidad', 'Cantidad:') !!}
    {!! Form::number('cantidad', null, ['class' => 'form-control']) !!}
</div>