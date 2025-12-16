<!-- Descripcion Field -->
<div class="form-group col-sm-6">
    {!! Form::label('descripcion', 'Descripcion:') !!}
    {!! Form::text('descripcion', null, ['class' => 'form-control', 'required', 'placeholder' => 'Ingrese la Descripcion de la Caja']) !!}
</div>

<!-- Id Sucursal Field -->
<div class="form-group col-sm-6">
    {!! Form::label('id_sucursal', 'Sucursal:') !!}
    {!! Form::select('id_sucursal',$sucursales, null, [
        'class' => 'form-control', 'required', 'placeholder' => 'Seleccione la Sucursal']) !!}
</div>

<!-- Punto Expedicion Field -->
<div class="form-group col-sm-6">
    {!! Form::label('punto_expedicion', 'Punto Expedicion:') !!}
    {!! Form::text('punto_expedicion', null, ['class' => 'form-control', 'required', 'placeholder' => 'Ingrese el Punto de Expedicion de la Caja']) !!}
</div>

<!-- Ultima Factura Impresa Field -->
<div class="form-group col-sm-6">
    {!! Form::label('ultima_factura_impresa', 'Ultima Factura Impresa:') !!}
    {!! Form::number('ultima_factura_impresa', null, ['class' => 'form-control', 'required', 'placeholder' => 'Ingrese la Ultima Factura Impresa de la Caja']) !!}
</div>