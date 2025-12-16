<!-- Descripcion Field -->
<div class="col-sm-12">
    {!! Form::label('descripcion', 'Descripcion:') !!}
    <p>{{ $cajas->descripcion }}</p>
</div>

<!-- Id Sucursal Field -->
<div class="col-sm-12">
    {!! Form::label('id_sucursal', 'Id Sucursal:') !!}
    <p>{{ $cajas->id_sucursal }}</p>
</div>

<!-- Punto Expedicion Field -->
<div class="col-sm-12">
    {!! Form::label('punto_expedicion', 'Punto Expedicion:') !!}
    <p>{{ $cajas->punto_expedicion }}</p>
</div>

<!-- Ultima Factura Impresa Field -->
<div class="col-sm-12">
    {!! Form::label('ultima_factura_impresa', 'Ultima Factura Impresa:') !!}
    <p>{{ $cajas->ultima_factura_impresa }}</p>
</div>

