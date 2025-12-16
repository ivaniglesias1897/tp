<!-- Name Field -->
<div class="col-sm-12">
    {!! Form::label('name', 'Name:') !!}
    <p>{{ $users->name }}</p>
</div>

<!-- Email Field -->
<div class="col-sm-12">
    {!! Form::label('email', 'Email:') !!}
    <p>{{ $users->email }}</p>
</div>

<!-- Email Verified At Field -->
<div class="col-sm-12">
    {!! Form::label('email_verified_at', 'Email Verified At:') !!}
    <p>{{ $users->email_verified_at }}</p>
</div>

<!-- Password Field -->
<div class="col-sm-12">
    {!! Form::label('password', 'Password:') !!}
    <p>{{ $users->password }}</p>
</div>

<!-- Remember Token Field -->
<div class="col-sm-12">
    {!! Form::label('remember_token', 'Remember Token:') !!}
    <p>{{ $users->remember_token }}</p>
</div>

<!-- Ci Field -->
<div class="col-sm-12">
    {!! Form::label('ci', 'Ci:') !!}
    <p>{{ $users->ci }}</p>
</div>

<!-- Direccion Field -->
<div class="col-sm-12">
    {!! Form::label('direccion', 'Direccion:') !!}
    <p>{{ $users->direccion }}</p>
</div>

<!-- Telefono Field -->
<div class="col-sm-12">
    {!! Form::label('telefono', 'Telefono:') !!}
    <p>{{ $users->telefono }}</p>
</div>

<!-- Fecha Ingreso Field -->
<div class="col-sm-12">
    {!! Form::label('fecha_ingreso', 'Fecha Ingreso:') !!}
    <p>{{ $users->fecha_ingreso }}</p>
</div>

