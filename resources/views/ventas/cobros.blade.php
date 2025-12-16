@extends('layouts.app')

@section('content')
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-12">
                    <h1>
                        Cobrar
                    </h1>
                </div>
            </div>
        </div>
    </section>

    <div class="content px-3">

        @include('sweetalert::alert')
        <div class="card">

            {!! Form::open(['route' => 'cobros.store']) !!}

            <div class="card-body">
                <!-- cabecera de ventas -->
                <div class="row">
                    {!! Form::hidden('id_venta', $ventas->id_venta, ['class' => 'form-control']) !!}
                    <!-- Ven Fecha Field -->
                    <div class="form-group col-sm-6">
                        {!! Form::label('ven_fecha', 'Fecha Venta:') !!}
                        {!! Form::date(
                            'ven_fecha',
                            isset($ventas)
                                ? \Carbon\Carbon::parse($ventas->fecha_venta)->format('Y-m-d')
                                : \Carbon\Carbon::now()->format('Y-m-d'),
                            ['class' => 'form-control', 'id' => 'ven_fecha', 'readonly' => 'readonly'],
                        ) !!}
                    </div>

                    <!-- Nro Factura Field -->
                    <div class="form-group col-sm-6">
                        {!! Form::label('nro_factura', 'Nro Factura:') !!}
                        {!! Form::text('nro_factura', $ventas->factura_nro, ['class' => 'form-control', 'readonly' => 'readonly']) !!}
                    </div>

                    <!-- Id Cliente Field -->
                    <div class="form-group col-sm-6">
                        {!! Form::label('id_cliente', 'Cliente:') !!}
                        {!! Form::text('id_cliente', $ventas->cliente, [
                            'class' => 'form-control',
                            'readonly' => 'readonly',
                        ]) !!}
                    </div>

                    <div class="form-group col-sm-6">
                        {!! Form::label('vtot_fac', 'Importe a Pagar:') !!}
                        {!! Form::text('vtot_fac', number_format($ventas->total, 0, ',', '.'), [
                            'class' => 'form-control',
                            'readonly' => 'readonly',
                            'id' => 'vtot_fac',
                        ]) !!}
                    </div>
                </div>

                <!-- DETALLE DE COBRO -->
                <div class="row">
                    <table class="table listado_for_pago">
                        <thead>
                            <tr>
                                <th style="width:35%;min-width:240px;">Forma de cobro</th>
                                <th class="text-center" style="width:20%;">Importe</th>
                                <th class="text-center">Nro Voucher</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody></tbody>

                        <tfoot>
                            <tr>
                                <td colspan="3">
                                    {{-- AGREGAR ID AL BOTÓN PARA SELECCIÓN SEGURA --}}
                                    <a href="javascript:void(0);" class='btn btn-primary btn-sm' id="btn-add-row-cobro">
                                        <i class="fa fa-plus"></i> Agregar
                                    </a>
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </div>

                <div class="row">
                    <div class="form-group col-sm-6">
                        {!! Form::label('pendiento_cobro', 'Pendiente:') !!}
                        {!! Form::text('pendiento_cobro', number_format($ventas->total, 0, ',', '.'), [
                            'class' => 'form-control text-right',
                            'readonly' => 'readonly',
                            'id' => 'vtot_pend',
                        ]) !!}
                    </div>

                    <div class="form-group col-sm-6">
                        {!! Form::label('total_cobro', 'Total Pago:') !!}
                        {!! Form::text('total_cobro', 0, [
                            'class' => 'form-control text-right vtot_fpa',
                            'readonly' => 'readonly',
                        ]) !!}
                        <input type="hidden" id="tot_fpa" name="tot_fpa">
                    </div>
                </div>
            </div>

            <div class="card-footer">
                {!! Form::submit('Pagar', ['class' => 'btn btn-primary']) !!}
                <a href="{{ route('ventas.index') }}" class="btn btn-default"> Cancelar </a>
            </div>

            {!! Form::close() !!}

        </div>

        <template tpl-cobros>
            <tr>
                <td>
                    {{-- Usamos el array de metodos_pago para generar el SELECT --}}
                    {!! Form::select('forma_pago[]', $metodos_pago, null, [
                        'class' => 'form-control select2',
                        'style' => 'width: 100%',
                        'id' => 'forma_pago',
                    ]) !!}
                    {!! Form::hidden('id_cobro[]', null) !!}
                </td>

                <td class="text-center">
                    <input class="form-control text-center totalFpa" type="text" min="1" name="importe[]"
                        onchange="actTotalFpa(this)" onkeyup="format(this);" style="text-align: center">
                </td>

                <td class="text-center" style="width: 20%">
                    <input class="form-control text-center" type="text" name="nro_voucher[]" style="text-align: center">
                </td>

                <td class="text-center">
                    <a href="javascript:void(0);" class="btn btn-danger" title="Eliminar Fila" onclick="eliminarFila(this)">
                        <i class="far fa-trash-alt "></i>
                    </a>
                </td>
            </tr>
        </template>
    </div>
@endsection

@push('scripts') {{-- <<--- CAMBIO CLAVE A 'scripts' --}}
    {{-- Mover las funciones globales fuera de $(document).ready() para que sean accesibles desde onchange/onclick --}}
    <script type="text/javascript">

        // --- FUNCIONES GLOBALES (ACCESIBLES DESDE ONCLICK/ONCHANGE) ---

        /** FUNCION PARA ELIMINAR FILA DE UNA TABLA **/
        function eliminarFila(t) {
            // remover el tr completo de forma de pago
            $(t).parents('tr').remove();    
            // actualizar totales llamando a la funcion actTotFpa()
            actTotFpa();
        }

        /** Funcion para calcular subtotal de forma de pagos **/
        function actTotalFpa(t) {
            var error = false;
            // Aseguramos que 't' sea el elemento, si es undefined, salimos
            if (!t) return; 

            // Reemplazamos todos los puntos para obtener el valor numérico (ej: 1.000.000 -> 1000000)
            var totalFpa = $(t).val().replace(/\./g, ''); 
            var totalFac = $("#vtot_fac").val().replace(/\./g, '');
            
            // Convertimos a número. Si es NaN, es un error.
            if (isNaN(parseInt(totalFpa)) || parseInt(totalFpa) <= 0) { 
                Swal.fire({
                    title: 'Error!',
                    text: 'Ingrese un número válido mayor a cero',
                    icon: 'info',
                    confirmButtonText: 'Ok'
                });
                error = true;
            } 
            // Si el valor del input es menor a cero (aunque ya lo valida el if anterior)
            else if (parseInt(totalFpa) < 0) { 
                Swal.fire({
                    title: 'Error!',
                    text: 'Ingrese un número mayor a cero',
                    icon: 'info',
                    confirmButtonText: 'Ok'
                });
                error = true;
            }

            // Si hay un error, reseteamos el valor de la fila actual y actualizamos el total general
            if (error) {
                $(t).val(formatMoney(0)); // Resetea el input a 0 con formato
                actTotFpa();
                return;
            } else {
                // Si no hay error, le da formato al input y recalcula el total general
                $(t).val(formatMoney(totalFpa));
                actTotFpa();
            }
        }


        /** Funcion para calcular y actualizar el total de forma de pagos y el pendiente **/
        function actTotFpa() {
            var total = 0,
                totfac = $("#vtot_fac").val().replace(/\./g, '');
            
            // Recorre todas las filas de la tabla y suma los importes
            $('.listado_for_pago tbody tr').each(function(idx, el) {
                // Usamos 0 si el valor no es un número válido después de limpiar los puntos
                var monto = parseInt($(el).find("[name='importe[]']").val().replace(/\./g, '')) || 0;
                total += monto;
            });

            // Si el total cobrado supera el total de la factura, alertamos y ajustamos
            if (total > totfac && totfac > 0) {
                Swal.fire({
                    title: 'Error!',
                    text: 'El total cobrado (' + formatMoney(total) + ') supera el Importe a Pagar (' + formatMoney(totfac) + ')',
                    icon: 'error',
                    confirmButtonText: 'Entendido'
                });
            }


            // Actualiza los campos visibles y ocultos
            $(".vtot_fpa").val(formatMoney(total));
            $("#tot_fpa").val(total);
            $("#vtot_pend").val(formatMoney(totfac - total));
        }

        /** Formato de miles (no modificado) **/
        function formatMoney(n, c, d, t) {
            let s, i, j;
            c = isNaN(c = Math.abs(c)) ? 0 : c;
            d = d === undefined ? "," : d;
            t = t === undefined ? "." : t;
            s = n < 0 ? "-" : "";
            i = String(parseInt(n = Math.abs(Number(n) || 0).toFixed(c)));
            j = (j = i.length) > 3 ? j % 3 : 0;
            return s + (j ? i.substr(0, j) + t : "") + i.substr(j).replace(/(\d{3})(?=\d)/g, "$1" + t) +
                (c ? d + Math.abs(n - i).toFixed(c).slice(2) : "");
        }


        // --- LÓGICA DE CLIC DEL BOTÓN (DENTRO DE READY) ---
        $(document).ready(function() {
            /** evitar submit con el boton enter **/
            $("form").keypress(function(e) {
                if (e.which == 13) {
                    return false;
                }
            });

            /** funcion clic para clonar filas tr para la tabla de pagos **/
            // Usamos el ID seguro para el evento de clic
            $("#btn-add-row-cobro").on('click', function(e) {
                e.preventDefault(); 
                const $this = $(this);
                
                // tabla padre 
                const tableRef = $this.parents(".listado_for_pago");
                
                // Obtener el contenido del template
                const row_pagos = document.querySelector('[tpl-cobros]').content.cloneNode(true);

                // verificar que exista datos en row_pagos
                if ($(row_pagos).length > 0) {
                    // agregar datos al body de la tabla con append()
                    tableRef.find("tbody").append(row_pagos);
                    
                    // Inicializar Select2 en el nuevo SELECT si estás usando Select2
                    tableRef.find("tbody tr:last-child").find('.select2').select2({
                         placeholder: "Seleccione método",
                         allowClear: true
                    });
                }
                $this.removeClass('disabled');
            });
            
            // La inicialización global de Select2 ya está en app.blade.php, esta línea puede ser omitida.
        });

    </script>
@endpush