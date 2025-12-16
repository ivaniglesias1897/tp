<!-- Descripcion Field -->
<div class="form-group col-sm-12">
    {!! Form::label('descripcion', 'Descripcion:') !!}
    {!! Form::text('descripcion', null, ['class' => 'form-control',
    'placeholder' => 'Ingrese la Descripcion',
    'required']) !!}
</div>