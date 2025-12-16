{!! Form::hidden('id_apertura', $ventas->id_apertura ?? null, ['class' => 'form-control']) !!}

<div class="form-group col-sm-4">
    {!! Form::label('fecha_venta', 'Fecha Venta:') !!}
    {!! Form::date('fecha_venta', $ventas->fecha_venta, [
        'class' => 'form-control',
        'id' => 'fecha_venta',
        'disabled' => 'disabled' // Bloqueado
    ]) !!}
</div>

<div class="form-group col-sm-4">
    {!! Form::label('factura_nro', 'Factura Nro:') !!}
    {!! Form::text('factura_nro', $ventas->factura_nro, [
        'class' => 'form-control', 
        'readonly' => 'readonly',
        'disabled' => 'disabled' // Bloqueado visualmente también
    ]) !!}
</div>

<div class="form-group col-sm-4">
    {!! Form::label('user_id', 'Responsable:') !!}
    {!! Form::text('user_name', $usuario, [
        'class' => 'form-control', 
        'readonly' => 'readonly',
        'disabled' => 'disabled'
    ]) !!}
</div>

<div class="form-group col-sm-4">
    {!! Form::label('id_cliente', 'Cliente:') !!}
    {!! Form::select('id_cliente', $clientes, $ventas->id_cliente, [
        'class' => 'form-control',
        'placeholder' => 'Seleccione un cliente',
        'disabled' => 'disabled' // El select ya no se podrá abrir
    ]) !!}
</div>

<div class="form-group col-sm-4">
    {!! Form::label('condicion_venta', 'Condición de Venta:') !!}
    {!! Form::select('condicion_venta', $condicion_venta, $ventas->condicion_venta, [
        'class' => 'form-control',
        'id' => 'condicion_venta',
        'disabled' => 'disabled' // Bloqueado
    ]) !!}
</div>

<div class="form-group col-sm-4">
    {!! Form::label('id_sucursal', 'Sucursal:') !!}
    {!! Form::select('id_sucursal', $sucursales, $ventas->id_sucursal, [
        'class' => 'form-control',
        'id' => 'id_sucursal',
        'disabled' => 'disabled' // Bloqueado
    ]) !!}
</div>

<div class="form-group col-sm-6" id="div-intervalo" style="{{ $ventas->condicion_venta == 'CONTADO' ? 'display: none;' : '' }}"> 
    {!! Form::label('intervalo', 'Intervalo de Vencimiento:') !!}
    {!! Form::select('intervalo', $intervalo_vencimiento, $ventas->intervalo, [
        'class' => 'form-control',
        'placeholder' => 'Seleccione un intervalo',
        'id' => 'intervalo',
        'disabled' => 'disabled' // Bloqueado
    ]) !!}
</div>

<div class="form-group col-sm-6" id="div-cantidad-cuota" style="{{ $ventas->condicion_venta == 'CONTADO' ? 'display: none;' : '' }}">
    {!! Form::label('cantidad_cuota', 'Cantidad Cuota:') !!}
    {!! Form::number('cantidad_cuota', $ventas->cantidad_cuota, [
        'class' => 'form-control',
        'placeholder' => 'Ingrese la cantidad de cuotas',
        'id' => 'cantidad_cuota',
        'disabled' => 'disabled' // Bloqueado
    ]) !!}
</div>

<div class="form-group col-sm-12"> 
    @includeIf('ventas.detalle')
</div>

<div class="form-group col-sm-6">
    {!! Form::label('total', 'Total:') !!}
    {!! Form::text('total', number_format($ventas->total, 0, ',', '.'), [
        'class' => 'form-control', 
        'id' => 'total', 
        'readonly' => 'readonly',
        'disabled' => 'disabled'
    ]) !!}
</div>

<style>
    /* Ocultar el botón de buscar producto solo en esta vista */
    .btn-buscar-producto, #buscar {
        display: none !important;
    }
    /* Ocultar columnas de acciones en la tabla de detalles (como eliminar) si tienen clase .acciones */
    .acciones {
        display: none !important;
    }
</style>

@includeIf('ventas.modal_producto')

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        $(document).ready(function() {
            // En modo VER, deshabilitamos interacciones extra si es necesario
            $('input, select, textarea').prop('disabled', true);
            
            // La lógica de mostrar/ocultar cuotas ya la manejamos con el style="" inline en el HTML arriba,
            // pero dejamos el script por si acaso, aunque al estar disabled el select, el change no se dispara.
        });
    </script>
@endpush