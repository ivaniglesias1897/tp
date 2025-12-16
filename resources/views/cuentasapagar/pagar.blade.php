@extends('layouts.app')

@section('content')
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0">Registrar Pago a Proveedor</h1>
            </div>
        </div>
    </div>
</div>

<div class="content">
    <div class="container-fluid">
        <div class="row justify-content-center">
            <div class="col-md-8">
                
                {{-- CARD 1: FORMULARIO DE NUEVO PAGO --}}
                <div class="card card-danger card-outline mb-4">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-file-invoice-dollar mr-1"></i>
                            Nueva Operación de Pago
                        </h3>
                    </div>
                    
                    <form action="{{ route('cuentasapagar.guardarPago', $cuenta->id_cta) }}" method="POST" id="form-pago">
                        @csrf
                        <div class="card-body">
                            {{-- Información de la Deuda --}}
                            <div class="row mb-4 bg-light p-3 rounded">
                                <div class="col-md-6">
                                    <strong>Proveedor:</strong> {{ $cuenta->proveedor }}<br>
                                    <strong>Factura Compra:</strong> {{ $cuenta->factura }}<br>
                                    <strong>Cuota:</strong> {{ $cuenta->nro_cuenta }} / {{ $cuenta->cantidad_cuotas ?? 'N/A' }}
                                </div>
                                <div class="col-md-6 text-right">
                                    <h5 class="text-muted">Saldo Pendiente:</h5>
                                    <h2 class="text-danger font-weight-bold">
                                        Gs. {{ number_format($cuenta->saldo ?? $cuenta->importe, 0, ',', '.') }}
                                    </h2>
                                    {{-- Input oculto para validaciones JS --}}
                                    <input type="hidden" id="saldo_pendiente" value="{{ $cuenta->saldo ?? $cuenta->importe }}">
                                </div>
                            </div>

                            {{-- Formulario: Solo visible si hay saldo pendiente --}}
                            @if(($cuenta->saldo ?? $cuenta->importe) > 0)
                                <hr>
                                <div class="form-group">
                                    <label>Detalle del Pago</label>
                                    <button type="button" class="btn btn-success btn-xs float-right" id="btn-agregar-pago">
                                        <i class="fas fa-plus"></i> Agregar Medio de Pago
                                    </button>
                                </div>

                                <div id="contenedor-pagos">
                                    {{-- Fila inicial --}}
                                    <div class="row item-pago mb-2">
                                        <div class="col-md-3">
                                            <select name="forma_pago[]" class="form-control select2" required>
                                                @foreach($metodos_pago as $id => $metodo)
                                                    <option value="{{ $id }}">{{ $metodo }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-md-3">
                                            <input type="text" name="nro_recibo[]" class="form-control" placeholder="Nro Recibo/Factura">
                                        </div>
                                        <div class="col-md-3">
                                            <input type="text" name="observacion[]" class="form-control" placeholder="Observación">
                                        </div>
                                        <div class="col-md-2">
                                            <input type="text" name="importe[]" class="form-control importe-input text-right" 
                                                   required placeholder="0" onkeyup="format(this); calcularTotal()">
                                        </div>
                                        <div class="col-md-1">
                                            {{-- Espacio para botón eliminar --}}
                                        </div>
                                    </div>
                                </div>

                                <div class="row mt-3">
                                    <div class="col-md-7 text-right">
                                        <h4>Total a Pagar:</h4>
                                    </div>
                                    <div class="col-md-5">
                                        <h3 class="text-success text-right" id="display_total">Gs. 0</h3>
                                    </div>
                                </div>
                            @else
                                <div class="alert alert-success text-center">
                                    <i class="fas fa-check-circle"></i> Esta cuenta está totalmente pagada.
                                </div>
                            @endif
                        </div>
                        
                        @if(($cuenta->saldo ?? $cuenta->importe) > 0)
                            <div class="card-footer text-right">
                                <a href="{{ route('cuentasapagar.index') }}" class="btn btn-secondary">Volver</a>
                                <button type="submit" class="btn btn-danger" id="btn-guardar">
                                    <i class="fas fa-save"></i> Confirmar Pago
                                </button>
                            </div>
                        @else
                            <div class="card-footer text-right">
                                <a href="{{ route('cuentasapagar.index') }}" class="btn btn-secondary">Volver</a>
                            </div>
                        @endif
                    </form>
                </div>

                {{-- CARD 2: HISTORIAL DE PAGOS (NUEVO) --}}
                @if(isset($pagosRealizados) && count($pagosRealizados) > 0)
                <div class="card card-secondary">
                    <div class="card-header">
                        <h3 class="card-title"><i class="fas fa-history mr-1"></i> Historial de Pagos</h3>
                    </div>
                    <div class="card-body p-0">
                        <table class="table table-sm table-striped">
                            <thead>
                                <tr>
                                    <th>Fecha</th>
                                    <th>Nro. Recibo</th>
                                    <th>Método</th>
                                    <th class="text-right">Monto</th>
                                    <th class="text-center">Estado</th>
                                    <th class="text-center">Acción</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($pagosRealizados as $pago)
                                    <tr>
                                        <td>{{ \Carbon\Carbon::parse($pago->fecha_pago)->format('d/m/Y') }}</td>
                                        <td>{{ $pago->nro_recibo ?? '-' }}</td>
                                        <td>{{ $pago->metodo }}</td>
                                        <td class="text-right">{{ number_format($pago->monto_pago, 0, ',', '.') }}</td>
                                        <td class="text-center">
                                            @if($pago->estado == 'ACTIVO')
                                                <span class="badge badge-success">ACTIVO</span>
                                            @else
                                                <span class="badge badge-secondary">ANULADO</span>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            @if($pago->estado == 'ACTIVO')
                                                <form action="{{ route('cuentasapagar.anularPago', $pago->id_pago) }}" method="POST" style="display:inline;">
                                                    @csrf
                                                    <button type="submit" class="btn btn-outline-danger btn-xs" 
                                                            onclick="return confirm('ATENCIÓN: ¿Está seguro de anular este pago? El saldo de la deuda aumentará y el pago quedará invalidado.')"
                                                            title="Anular Pago">
                                                        <i class="fas fa-times"></i> Anular
                                                    </button>
                                                </form>
                                            @else
                                                <span class="text-muted text-xs">-</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
                @endif

            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        // Inicializar Select2 en la primera fila
        $('.select2').select2();

        // Lógica para agregar nuevas filas de pago
        $('#btn-agregar-pago').click(function() {
            var fila = `
            <div class="row item-pago mb-2">
                <div class="col-md-3">
                    <select name="forma_pago[]" class="form-control select2-new" required>
                        @foreach($metodos_pago as $id => $metodo)
                            <option value="{{ $id }}">{{ $metodo }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <input type="text" name="nro_recibo[]" class="form-control" placeholder="Nro Recibo/Factura">
                </div>
                <div class="col-md-3">
                    <input type="text" name="observacion[]" class="form-control" placeholder="Observación">
                </div>
                <div class="col-md-2">
                    <input type="text" name="importe[]" class="form-control importe-input text-right" 
                           required placeholder="0" onkeyup="format(this); calcularTotal()">
                </div>
                <div class="col-md-1">
                    <button type="button" class="btn btn-danger btn-xs btn-remove-pago"><i class="fas fa-trash"></i></button>
                </div>
            </div>`;
            
            $('#contenedor-pagos').append(fila);
            
            // Inicializar select2 solo en el nuevo elemento
            $('.select2-new').select2();
            $('.select2-new').removeClass('select2-new');
        });

        // Eliminar fila de pago
        $(document).on('click', '.btn-remove-pago', function() {
            $(this).closest('.item-pago').remove();
            calcularTotal();
        });
    });

    // Calcular el total en tiempo real y validar contra el saldo
    function calcularTotal() {
        let total = 0;
        $('.importe-input').each(function() {
            let val = $(this).val().replace(/\./g, '');
            if(val !== '') total += parseInt(val);
        });

        $('#display_total').text('Gs. ' + total.toLocaleString('es-PY'));

        let saldo = parseInt($('#saldo_pendiente').val());
        
        // Validación visual: Rojo si supera el saldo, Verde si está bien
        if(total > saldo) {
            $('#display_total').removeClass('text-success').addClass('text-danger');
            $('#btn-guardar').prop('disabled', true); // Bloqueamos el botón para evitar enviar datos erróneos
            Swal.fire('Atención', 'El monto a pagar supera el saldo pendiente de la deuda', 'warning');
        } else {
            $('#display_total').removeClass('text-danger').addClass('text-success');
            $('#btn-guardar').prop('disabled', false);
        }
    }

    // Formato de miles (1.000.000)
    function format(input) {
        var num = input.value.replace(/\./g, '');
        if (!isNaN(num)) {
            num = num.split('').reverse().join('').replace(/(\d{3})(?=\d)/g, '$1.').split('').reverse().join('');
            input.value = num;
        } else {
            input.value = input.value.replace(/[^\d]/g, '');
        }
    }
</script>
@endpush