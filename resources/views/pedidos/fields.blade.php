<!-- Id Apertura Field -->
{!! Form::hidden('id_apertura', null, ['class' => 'form-control']) !!}
<!-- Fecha Pedido Field -->
<div class="form-group col-sm-4">
    {!! Form::label('fecha_pedido', 'Fecha Pedido:') !!}
    {!! Form::date('fecha_pedido', \Carbon\Carbon::now()->format('Y-m-d'), [
        'class' => 'form-control',
        'id' => 'fecha_pedido',
        'required',
        'readonly',
    ]) !!}
</div>

<!-- Factura Nro Field -->
<div class="form-group col-sm-4">
    {!! Form::label('factura_nro', 'Factura Nro:') !!}
    {!! Form::text('factura_nro', null, ['class' => 'form-control', 'readonly']) !!}
</div>

<!-- User Id Field -->
<div class="form-group col-sm-4">
    {!! Form::label('user_id', 'Responsable:') !!}
    {!! Form::text('user_name', $usuario, ['class' => 'form-control', 'readonly']) !!}
    {!! Form::hidden('user_id', auth()->user()->id, ['class' => 'form-control']) !!}
</div>

<!-- Id Cliente Field -->
<div class="form-group col-sm-4">
    {!! Form::label('id_cliente', 'Cliente:') !!}
    {!! Form::select('id_cliente', $clientes, null, [
        'class' => 'form-control',
        'required',
        'placeholder' => 'Seleccione un cliente',
    ]) !!}
</div>
<!-- sucursal -->
<div class="form-group col-sm-4">
    {!! Form::label('id_sucursal', 'Sucursal:') !!}
    {!! Form::select('id_sucursal', $sucursales, null, [
        'class' => 'form-control',
        'id' => 'id_sucursal',
        'required',
    ]) !!}
</div>

<!-- Cantidad cuota Field -->
<div class="form-group col-sm-6" id="div-cantidad-cuota" style="display: none;">
    {!! Form::label('cantidad_cuota', 'Cantidad Cuota:') !!}
    {!! Form::number('cantidad_cuota', null, [
        'class' => 'form-control',
        'placeholder' => 'Ingrese la cantidad de cuotas',
        'id' => 'cantidad_cuota'
    ]) !!}
</div>

<!-- Detalle de pedido -->
<div class="form-group col-sm-12"> 
    @includeIf('pedidos.detalle')
</div>


<!-- Total Field -->
<div class="form-group col-sm-6">
    {!! Form::label('total', 'Total:') !!}
    {!! Form::text('total', isset($pedido) ? number_format($pedido->total_pedido, 0, ',', '.') : null, ['class' => 'form-control', 'id' => 'total', 'readonly']) !!}
</div>

@includeIf('pedidos.modal_producto')

<!-- Js -->
@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // comenzar la carga con document ready
        $(document).ready(function() {
            
            /** CONSULTAR AJAX PARA LLENAR POR DEFECTO EL MODAL AL ABRIR SE CONSULTA LA URL */
            document.getElementById('buscar').addEventListener('click', function() {
                $('#productSearchModal').modal('show'); // Mostrar el modal
                fetch('{{ url('buscar-productos') }}?cod_suc=' + $("#id_sucursal").val())// capturar valor de sucursal utilzando val()
                    .then(response => response.text())
                    .then(html => {
                        document.getElementById('modalResults').innerHTML = html; // innerHTML es para cargar en el modal
                    })
                    .catch(error => {
                        console.error('Error:', error); // mostrar error en consola
                    });
            });

            // Ocultar o mostra campos segun seleccion de condicion de pedido
            $("#condicion_pedido").on("change", function() {
                var condicion_pedido = $(this).val();// es capturar el dato selecciona con this.val()
                if(condicion_pedido == 'CONTADO') {
                    //hide es para ocultar
                    $("#div-intervalo").hide();
                    $("#div-cantidad-cuota").hide();
                    // prop es para asignar una propiedad al campo input y decirle no requerido
                    $("#intervalo").prop('required', false);
                    $("#cantidad_cuota").prop('required', false);
                } else {
                    //show es para mostrar
                    $("#div-intervalo").show(); 
                    $("#div-cantidad-cuota").show();
                    // prop es para asignar una propiedad al campo input y decirle es requerido
                    $("#intervalo").prop('required', true);
                    $("#cantidad_cuota").prop('required', true);
                }
            });
        });
    </script>
@endpush