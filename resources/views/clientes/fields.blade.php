<!-- Clie Nombre Field -->
<div class="form-group col-sm-3">
    {!! Form::label('clie_nombre', 'Nombre:') !!}
    {!! Form::text('clie_nombre', null, ['class' => 'form-control', 'required', 'placeholder' => 'Ingrese el Nombre']) !!}
</div>

<!-- Cli Apellido Field -->
<div class="form-group col-sm-3">
    {!! Form::label('clie_apellido', 'Apellido:') !!}
    {!! Form::text('clie_apellido', null, ['class' => 'form-control', 'required', 'placeholder' => 'Ingrese el Apellido']) !!}
</div>

<!-- Clie Ci Field -->
<div class="form-group col-sm-3">
    {!! Form::label('clie_ci', 'Nro. Documento:') !!}
    {!! Form::text('clie_ci', null, ['class' => 'form-control', 'required', 'placeholder' => 'Ingrese el Nro. de Documento']) !!}
</div>

<!-- Clie Telefono Field -->
<div class="form-group col-sm-3">
    {!! Form::label('clie_telefono', 'Telefono:') !!}
    {!! Form::text('clie_telefono', null, ['class' => 'form-control', 'placeholder' => 'Ingrese el Telefono']) !!}
</div>

<!-- Clie Direccion Field -->
<div class="form-group col-sm-12 col-lg-4">
    {!! Form::label('clie_direccion', 'Direccion:') !!}
    {!! Form::textarea('clie_direccion', null, ['class' => 'form-control', 'rows' => 3, 'placeholder' => 'Ingrese la Direccion']) !!}
</div>

<!-- Id Departamento Field -->
<div class="form-group col-sm-4">
    {!! Form::label('id_departamento', 'Departamento:') !!}
    {!! Form::select('id_departamento',$departamentos, null,
     ['class' => 'form-control', 'placeholder' => 'Seleccione el Departamento']) !!}
</div>

<!-- Id Ciudad Field -->
<div class="form-group col-sm-4">
    {!! Form::label('id_ciudad', 'Ciudad:') !!}
    {!! Form::select('id_ciudad',$ciudades, null, 
    ['class' => 'form-control', 'placeholder' => 'Seleccione la Ciudad']) !!}
</div>

<!-- Clie Fecha Nac Field -->
<div class="form-group col-sm-3">
    {!! Form::label('clie_fecha_nac', 'Fecha de Nacimiento:') !!}
    {!! Form::date('clie_fecha_nac', null, ['class' => 'form-control','id'=>'clie_fecha_nac', 'required', 'placeholder' => 'Ingrese la Fecha de Nacimiento']) !!}
</div>


