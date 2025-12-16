<!-- Proveedor -->
<div class="form-group col-sm-6">
    {!! Form::label('id_proveedor', 'Proveedor:') !!}
    {!! Form::select('id_proveedor', $proveedores ?? [], isset($compra) ? $compra->id_proveedor : null, [
        'class' => 'form-control select2',
        'placeholder' => 'Seleccione un proveedor',
        'required'
    ]) !!}
</div>

<!-- Fecha -->
<div class="form-group col-sm-3">
    {!! Form::label('fecha_compra', 'Fecha Compra:') !!}
    {!! Form::date('fecha_compra', isset($compra) ? \Carbon\Carbon::parse($compra->fecha_compra)->format('Y-m-d') : \Carbon\Carbon::now()->format('Y-m-d'), [
        'class' => 'form-control',
        'required'
    ]) !!}
</div>

<!-- Factura Nro Field (CON MÁSCARA AUTOMÁTICA) -->
<div class="form-group col-sm-3">
    {!! Form::label('factura', 'Factura Nro:') !!}
    {!! Form::text('factura', null, [
        'class' => 'form-control', 
        'id' => 'factura', 
        'placeholder' => '000-000-0000000',
        'maxlength' => '15' 
    ]) !!}
    <small class="text-muted">Formato: 001-001-0000001</small>
</div>

<!-- Usuario (solo visual) - TU CÓDIGO -->
<div class="form-group col-sm-3">
    {!! Form::label('usuario', 'Usuario:') !!}
    <input type="text" class="form-control" value="{{ auth()->user()->name ?? '' }}" readonly>
</div>

<!-- Total -->
<div class="form-group col-sm-3">
    {!! Form::label('total', 'Total:') !!}
    {!! Form::text('total', isset($compra) ? number_format($compra->total ?? 0, 0, ',', '.') : '0', [
        'class' => 'form-control text-right',
        'readonly',
        'id' => 'total'
    ]) !!}
</div>

<!-- Condicion Compra Field -->
<div class="form-group col-sm-3">
    {!! Form::label('condicion_compra', 'Condición de Compra:') !!}
    {!! Form::select('condicion_compra', $condicion_compra, null, [
        'class' => 'form-control',
        'id' => 'condicion_compra',
        'required',
    ]) !!}
</div>

<!-- Sucursal -->
<div class="form-group col-sm-3">
    {!! Form::label('id_sucursal', 'Sucursal:') !!}
    {!! Form::select('id_sucursal', $sucursales, isset($compra) ? $compra->id_sucursal : auth()->user()->id_sucursal, [
        'class' => 'form-control',
        'id' => 'id_sucursal',
        'required',
    ]) !!}
</div>

<!-- Div Crédito (Oculto por defecto) -->
<div class="col-12">
    <div class="row" id="campos_credito" style="display: {{ (isset($compra) && $compra->condicion_compra == 'CREDITO') || old('condicion_compra') == 'CREDITO' ? 'flex' : 'none' }};">
        <!-- Intervalo de Vencimiento Field -->
        <div class="form-group col-sm-6"> 
            {!! Form::label('intervalo', 'Intervalo de Vencimiento:') !!}
            {!! Form::select('intervalo', $intervalo, null, [
                'class' => 'form-control',
                'placeholder' => 'Seleccione un intervalo',
                'id' => 'intervalo'
            ]) !!}
        </div>

        <!-- Cantidad cuota Field -->
        <div class="form-group col-sm-6">
            {!! Form::label('cantidad_cuotas', 'Cantidad de Cuotas:') !!}
            {!! Form::number('cantidad_cuotas', null, [
                'class' => 'form-control',
                'placeholder' => 'Ingrese la cantidad de cuotas',
                'id' => 'cantidad_cuotas',
                'min' => 1
            ]) !!}
        </div>
    </div>
</div>

<!-- Detalle de Compra (Tabla) -->
<div class="form-group col-sm-12">
    @includeIf('compras.detalle')
</div>

{{-- Modal de Búsqueda de Productos --}}
@include('compras.modal_producto')

<!-- Campo oculto para enviar el detalle al controlador -->
<input type="hidden" id="detalle_data" name="detalle_data">

@push('scripts')
    <script>
        $(document).ready(function() {
            
            // --- 1. MÁSCARA AUTOMÁTICA PARA FACTURA ---
            $('#factura').on('input', function() {
                var val = $(this).val().replace(/\D/g, ''); // Solo números
                var newVal = '';
                // Formato 000-000-0000000
                if (val.length > 3) {
                    newVal += val.substr(0, 3) + '-';
                    if (val.length > 6) {
                        newVal += val.substr(3, 3) + '-';
                        newVal += val.substr(6, 7); 
                    } else {
                        newVal += val.substr(3);
                    }
                } else {
                    newVal = val;
                }
                $(this).val(newVal);
            });

            // --- 2. LÓGICA DE CRÉDITO/CONTADO ---
            $('#condicion_compra').on('change', function() {
                if ($(this).val() === 'CREDITO') {
                    $('#campos_credito').show();
                    $('#intervalo').prop('required', true);
                    $('#cantidad_cuotas').prop('required', true);
                } else {
                    $('#campos_credito').hide();
                    $('#intervalo').prop('required', false);
                    $('#cantidad_cuotas').prop('required', false);
                    // Limpiar valores al cambiar a contado
                    $('#intervalo').val('');
                    $('#cantidad_cuotas').val('');
                }
            }).trigger('change'); // Ejecutar al cargar

            // --- 3. MODAL DE PRODUCTOS (JQuery Seguro) ---
            $('#buscar').on('click', function() {
                $('#productSearchModal').modal('show');
                // Si hay texto previo, buscar de nuevo, si no, buscar todo
                var query = $('#productSearchQuery').val();
                cargarProductos(query);
            });

            // Búsqueda en vivo dentro del modal
            $(document).on('input', '#productSearchQuery', function() {
                var textoBusqueda = $(this).val();
                cargarProductos(textoBusqueda);
            });

            function cargarProductos(query = '') {
                // Ajusta la URL según tu ruta real
                var urlBuscar = "{{ url('buscar-productoscompras') }}"; // Asegúrate que esta ruta exista en web.php
                // Si usas la misma ruta de ventas, cámbiala a "{{ url('buscar-productos') }}"
                
                // Añadimos parámetros
                var fullUrl = urlBuscar + "?query=" + encodeURIComponent(query);
                
                fetch(fullUrl)
                    .then(response => response.text())
                    .then(html => {
                        $('#modalResults').html(html);
                    })
                    .catch(error => console.error('Error:', error));
            }

            // --- 4. GUARDAR DETALLE ANTES DE ENVIAR (Submit) ---
            $('form').on('submit', function(event) {
                const detalleData = [];
                $(".detalle-compra tbody tr").each(function() { // Asegúrate que tu tabla tenga clase .detalle-compra o ajusta el selector
                    const row = $(this);
                    // Ajusta los selectores [name='...'] según tu archivo compras/detalle.blade.php
                    const precioRaw = row.find("[name='precio[]']").val() || '0';
                    
                    const detalle = {
                        codigo: row.find("[name='codigo[]']").val(), // ID Producto
                        cantidad: row.find("[name='cantidad[]']").val(),
                        precio: parseFloat(precioRaw.replace(/\./g, '')), // Quitar puntos de miles
                    };
                    detalleData.push(detalle);
                });
                // Aunque enviamos los arrays nativos de HTML, esto sirve si usas validación JS extra
                // $("#detalle_data").val(JSON.stringify(detalleData));
            });
        });
    </script>
@endpush