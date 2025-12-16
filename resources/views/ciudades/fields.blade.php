<!-- Descripcion Field -->
<div class="form-group col-sm-6">
    {!! Form::label('descripcion', 'Descripcion:') !!}
    {!! Form::text('descripcion', null, [
        'class' => 'form-control',
        'required',
        'placeholder' => 'Ingrese la Descripcion de la Ciudad',
    ]) !!}
</div>

<!-- Id Departamento Field -->
<div class="form-group col-sm-6">
    {!! Form::label('id_departamento', 'Departamento:') !!}
    {!! Form::select('id_departamento',$departamentos, null, [
        'class' => 'form-control',
        'required',
        'placeholder' => 'Seleccione el Departamento',
    ]) !!}
</div>
