<!-- Name Field -->
<div class="form-group col-sm-6">
    {!! Form::label('name', 'Nombre y Apellido:') !!}
    {!! Form::text('name', null, [
        'class' => 'form-control',
        'required',
        'placeholder' => 'Ingrese el Nombre y Apellido',
    ]) !!}
</div>

<!-- Email Field -->
<div class="form-group col-sm-6">
    {!! Form::label('email', 'Nick Name:') !!}
    {!! Form::text('email', null, ['class' => 'form-control', 'required', 'placeholder' => 'Ingrese el Nick Name']) !!}
</div>


<!-- password Field -->
<div class="form-group col-sm-6">
    {!! Form::label('password', 'Ingresar Contraseña:') !!}
    {!! Form::password('password', [
        'class' => 'form-control',
        isset($users) ? '' : 'required',
        'placeholder' => isset($users) ? 'Dejar vacio si no se actualiza' : 'Ingrese la Contraseña',
    ]) !!}
</div>


<!-- Ci Field -->
<div class="form-group col-sm-6">
    {!! Form::label('ci', 'Nro Documento:') !!}
    {!! Form::text('ci', null, ['class' => 'form-control', 'required', 'placeholder' => 'Ingrese el Nro Documento']) !!}
</div>

<!-- Direccion Field -->
<div class="form-group col-sm-12 col-lg-12">
    {!! Form::label('direccion', 'Direccion:') !!}
    {!! Form::textarea('direccion', null, [
        'class' => 'form-control',
        'rows' => 3,
        'placeholder' => 'Ingrese la Dirección',
    ]) !!}
</div>

<!-- Telefono Field -->
<div class="form-group col-sm-6">
    {!! Form::label('telefono', 'Telefono:') !!}
    {!! Form::text('telefono', null, ['class' => 'form-control', 'placeholder' => 'Ingrese el nro de telefono']) !!}
</div>

<!-- Fecha Ingreso Field -->
<div class="form-group col-sm-6">
    {!! Form::label('fecha_ingreso', 'Fecha Ingreso:') !!}
    {!! Form::date('fecha_ingreso', null, ['class' => 'form-control', 'id' => 'fecha_ingreso', 'required']) !!}
</div>

<!-- Role Id Field -->
<div class="form-group col-sm-6">
    {!! Form::label('role_id', 'Rol:') !!}
    {!! Form::select('role_id', $roles, null, [
        'class' => 'form-control',
        'placeholder' => 'Seleccione un rol',
        'required',
    ]) !!}
</div>

<!-- Sucursal Id Field -->
<div class="form-group col-sm-6">
    {!! Form::label('id_sucursal', 'Sucursal:') !!}
    {!! Form::select('id_sucursal', $sucursales, null, [
        'class' => 'form-control',
        'placeholder' => 'Seleccione una sucursal',
        'required',
    ]) !!}
</div>
