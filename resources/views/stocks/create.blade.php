@extends('layouts.app')

@section('content')
<section class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-12">
                <h1>Registrar Ajuste de Stock</h1>
            </div>
        </div>
    </div>
</section>

<div class="content px-3">
    @include('adminlte-templates::common.errors')
    @include('sweetalert::alert')

    <div class="card">
        {!! Form::open(['route' => 'stocks.store']) !!}
        <div class="card-body">
            <div class="row">
                <!-- Select Producto (id_producto) -->
                <div class="form-group col-sm-6">
                    {!! Form::label('id_producto', 'Producto:') !!}
                    {{-- Usamos el array $productos cargado en el controlador --}}
                    {!! Form::select('id_producto', $productos, null, ['class' => 'form-control select2', 'placeholder' => 'Seleccione un producto', 'required']) !!}
                </div>

                <!-- Select Sucursal (id_sucursal) -->
                <div class="form-group col-sm-6">
                    {!! Form::label('id_sucursal', 'Sucursal:') !!}
                    {{-- Usamos el array $sucursales cargado en el controlador --}}
                    {!! Form::select('id_sucursal', $sucursales, null, ['class' => 'form-control select2', 'placeholder' => 'Seleccione una sucursal', 'required']) !!}
                </div>

                <!-- Tipo de Movimiento (tipo_movimiento) -->
                <div class="form-group col-sm-4">
                    {!! Form::label('tipo_movimiento', 'Tipo de Ajuste:') !!}
                    {{-- Usamos el array $tipos_ajuste cargado en el controlador (Entrada/Salida) --}}
                    {!! Form::select('tipo_movimiento', $tipos_ajuste, null, ['class' => 'form-control', 'required']) !!}
                </div>

                <!-- Cantidad a Ajustar (cantidad) -->
                <div class="form-group col-sm-4">
                    {!! Form::label('cantidad', 'Cantidad:') !!}
                    {{-- Usamos min=1 para asegurar que siempre se ajuste al menos una unidad --}}
                    {!! Form::number('cantidad', null, ['class' => 'form-control', 'min' => 1, 'placeholder' => 'Mínimo 1', 'required']) !!}
                </div>

                <!-- Observación / Motivo (observacion) -->
                <div class="form-group col-sm-4">
                    {!! Form::label('observacion', 'Observación / Motivo:') !!}
                    {!! Form::text('observacion', null, ['class' => 'form-control', 'placeholder' => 'Motivo del ajuste (ej: Inventario, Daño)']) !!}
                </div>
            </div>
        </div>

        <div class="card-footer">
            {!! Form::submit('Registrar Ajuste', ['class' => 'btn btn-primary']) !!}
            <a href="{{ route('stocks.index') }}" class="btn btn-default">Cancelar</a>
        </div>
        {!! Form::close() !!}
    </div>
</div>
@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        // Inicializar Select2
        $('.select2').select2({
            placeholder: "Selecciona una opción",
            allowClear: true,
            width: '100%'
        });
    });
</script>
@endpush