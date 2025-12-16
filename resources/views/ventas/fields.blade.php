<!-- Id Apertura Field -->
{!! Form::hidden('id_apertura', isset($apertura_caja) ? $apertura_caja->id_apertura : null, [
    'class' => 'form-control',
]) !!}

<!-- Fecha Venta Field -->
<div class="form-group col-sm-4">
    {!! Form::label('fecha_venta', 'Fecha Venta:') !!}
    {!! Form::date('fecha_venta', \Carbon\Carbon::now()->format('Y-m-d'), [
        'class' => 'form-control',
        'id' => 'fecha_venta',
        'required',
        'readonly',
    ]) !!}
</div>

<!-- Factura Nro Field -->
<div class="form-group col-sm-4">
    {!! Form::label('factura_nro', 'Factura Nro:') !!}
    {!! Form::text(
        'factura_nro',
        isset($apertura_caja)
            ? ($apertura_caja->establecimiento . '-' . $apertura_caja->punto_expedicion . '-' . $apertura_caja->nro_factura)
            : null,
        ['class' => 'form-control', 'readonly' => 'readonly'],
    ) !!}
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
        'class' => 'form-control select2',
        'required',
        'placeholder' => 'Seleccione un cliente',
    ]) !!}
</div>

<!-- Condicion venta Field -->
<div class="form-group col-sm-4">
    {!! Form::label('condicion_venta', 'Condición de Venta:') !!}
    {!! Form::select('condicion_venta', $condicion_venta, null, [
        'class' => 'form-control',
        'id' => 'condicion_venta',
        'required',
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

<!-- Intervalo de Vencimiento Field -->
<div class="form-group col-sm-6" id="div-intervalo" style="display: none;">
    {!! Form::label('intervalo', 'Intervalo de Vencimiento:') !!}
    {!! Form::select('intervalo', $intervalo_vencimiento, null, [
        'class' => 'form-control',
        'placeholder' => 'Seleccione un intervalo',
        'id' => 'intervalo',
    ]) !!}
</div>

<!-- Cantidad cuota Field -->
<div class="form-group col-sm-6" id="div-cantidad-cuota" style="display: none;">
    {!! Form::label('cantidad_cuota', 'Cantidad Cuota:') !!}
    {!! Form::number('cantidad_cuota', null, [
        'class' => 'form-control',
        'placeholder' => 'Ingrese la cantidad de cuotas',
        'id' => 'cantidad_cuota',
        'min' => '1'
    ]) !!}
</div>

<!-- Detalle de venta -->
<div class="form-group col-sm-12">
    @includeIf('ventas.detalle')
</div>

<!-- Total Field -->
<div class="form-group col-sm-6">
    {!! Form::label('total', 'Total:') !!}
    {!! Form::text('total', isset($ventas) ? number_format($ventas->total, 0, ',', '.') : null, [
        'class' => 'form-control',
        'id' => 'total',
        'readonly',
    ]) !!}
</div>

@includeIf('ventas.modal_producto')

<!-- definir un campo oculto para almacenar el detalle enviado -->
<input type="hidden" id="detalle_data" name="detalle_data">

<!-- Js -->
@push('scripts')
    
    <script>
        // comenzar la carga con document ready
        $(document).ready(function() {

            /** CONSULTAR AJAX PARA LLENAR POR DEFECTO EL MODAL AL ABRIR SE CONSULTA LA URL */
            // Validación por si el elemento 'buscar' no existe en esta vista (evita errores en consola)
            if(document.getElementById('buscar')){
                document.getElementById('buscar').addEventListener('click', function() {
                    $('#productSearchModal').modal('show'); // Mostrar el modal
                    fetch('{{ url('buscar-productos') }}?cod_suc=' + $("#id_sucursal").val()) 
                        .then(response => response.text())
                        .then(html => {
                            document.getElementById('modalResults').innerHTML = html; 
                        })
                        .catch(error => {
                            console.error('Error:', error); 
                        });
                });
            }

            // Ocultar o mostrar campos segun seleccion de condicion de venta
            $("#condicion_venta").on("change", function() {
                var condicion_venta = $(this).val(); 
                if (condicion_venta == 'CONTADO') {
                    //hide es para ocultar
                    $("#div-intervalo").hide();
                    $("#div-cantidad-cuota").hide();
                    // prop es para asignar una propiedad al campo input y decirle no requerido
                    $("#intervalo").prop('required', false);
                    $("#cantidad_cuota").prop('required', false);
                    
                    // Opcional: Limpiar valores al pasar a contado para evitar enviar basura
                    // $("#intervalo").val('').trigger('change');
                    // $("#cantidad_cuota").val('');
                } else {
                    //show es para mostrar
                    $("#div-intervalo").show();
                    $("#div-cantidad-cuota").show();
                    // prop es para asignar una propiedad al campo input y decirle es requerido
                    $("#intervalo").prop('required', true);
                    $("#cantidad_cuota").prop('required', true);
                }
            }).trigger('change'); // <--- ¡AQUÍ ESTÁ LA MAGIA! Esto ejecuta la lógica al cargar la página.

            // al enviar el formulario capturamos los datos del detalle
            $('form').on('submit', function(event) {
                // Capturar los datos de la tabla de detalles
                const detalleData = [];
                $(".detalle-venta tbody tr").each(function() {// recorrer cada fila de la tabla
                    const row = $(this);
                    const detalle = {
                        id_producto: row.find("[name='id_producto[]']").val(),
                        codigo: row.find("[name='codigo[]']").val(),
                        precio: parseFloat(row.find("[name='precio[]']").val().replace(/\./g, '')),
                        producto: row.find("[name='producto[]']").val(),
                        cantidad: row.find("[name='cantidad[]']").val(),
                        subtotal: parseFloat(row.find("[name='subtotal[]']").val().replace(/\./g, '')),
                    };
                    // agregar el objeto detalle al array
                    detalleData.push(detalle);
                });

                // Convertir los datos a JSON y asignarlos al campo oculto
                $("#detalle_data").val(JSON.stringify(detalleData));
            });

            // Repoblar la tabla con datos antiguos si existen
            const oldDetalleData = @json(old('detalle_data', '[]'));
            if (oldDetalleData.length > 0) {
                const detalleData = JSON.parse(oldDetalleData);
                detalleData.forEach(detalle => {
                    // Verificamos si existe la función antes de llamarla para evitar errores
                    if(typeof seleccionarProducto === 'function'){
                        seleccionarProducto(
                            detalle.codigo || '',
                            detalle.producto || '',
                            detalle.precio || 0,
                            detalle.subtotal || detalle.precio,
                            detalle.cantidad || 1
                        );
                    }
                });
            }
        });
    </script>
@endpush