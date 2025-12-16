@extends('layouts.app')

@section('content')
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0">Registrar Cobro de Cuota</h1>
            </div>
        </div>
    </div>
</div>

<div class="content">
    <div class="container-fluid">
        <div class="row justify-content-center">
            <div class="col-md-8">
                
                {{-- CARD DE NUEVO COBRO --}}
                <div class="card card-primary card-outline mb-4">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-file-invoice-dollar mr-1"></i>
                            Detalles de la Deuda
                        </h3>
                    </div>
                    
                    {{-- Formulario apuntando a la ruta de guardar cobro --}}
                    <form action="{{ route('cuentasacobrar.guardar', $cuenta->id_cta) }}" method="POST" id="form-cobro">
                        @csrf
                        <div class="card-body">
                            {{-- Info de la Cuenta --}}
                            <div class="row mb-4 bg-light p-3 rounded">
                                <div class="col-md-6">
                                    <strong>Cliente:</strong> {{ $cuenta->cliente }}<br>
                                    <strong>Factura Nro:</strong> {{ $cuenta->factura_nro }}<br>
                                    <strong>Cuota:</strong> {{ $cuenta->nro_cuota }} / {{ $cuenta->cantidad_cuota ?? 'N/A' }}
                                </div>
                                <div class="col-md-6 text-right">
                                    <h5 class="text-muted">Saldo Pendiente:</h5>
                                    {{-- Mostramos el saldo si existe, si no, el importe total --}}
                                    <h2 class="text-danger font-weight-bold">
                                        Gs. {{ number_format($cuenta->saldo ?? $cuenta->importe, 0, ',', '.') }}
                                    </h2>
                                    <input type="hidden" id="saldo_pendiente" value="{{ $cuenta->saldo ?? $cuenta->importe }}">
                                </div>
                            </div>

                            {{-- Formulario: Solo visible si hay saldo pendiente --}}
                            @if(($cuenta->saldo ?? $cuenta->importe) > 0)
                                <hr>
                                
                                {{-- Sección de Formas de Pago Dinámicas --}}
                                <div class="form-group">
                                    <label>Formas de Pago</label>
                                    <button type="button" class="btn btn-success btn-xs float-right" id="btn-agregar-pago">
                                        <i class="fas fa-plus"></i> Agregar Pago
                                    </button>
                                </div>

                                <div id="contenedor-pagos">
                                    {{-- Fila inicial --}}
                                    <div class="row item-pago mb-2">
                                        <div class="col-md-4">
                                            <select name="forma_pago[]" class="form-control select2" required>
                                                @foreach($metodos_pago as $id => $metodo)
                                                    <option value="{{ $id }}">{{ $metodo }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-md-3">
                                            <input type="text" name="nro_voucher[]" class="form-control" placeholder="Nro Ref/Voucher">
                                        </div>
                                        <div class="col-md-4">
                                            <input type="text" name="importe[]" class="form-control importe-input text-right" 
                                                   required placeholder="0" onkeyup="format(this); calcularTotal()">
                                        </div>
                                        <div class="col-md-1">
                                            {{-- El botón eliminar se agrega dinámicamente --}}
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
                                <a href="{{ route('cuentasacobrar.index') }}" class="btn btn-secondary">Cancelar</a>
                                <button type="submit" class="btn btn-primary" id="btn-guardar">
                                    <i class="fas fa-save"></i> Confirmar Cobro
                                </button>
                            </div>
                        @else
                            <div class="card-footer text-right">
                                <a href="{{ route('cuentasacobrar.index') }}" class="btn btn-secondary">Volver</a>
                            </div>
                        @endif
                    </form>
                </div>

                {{-- CARD DE HISTORIAL DE COBROS (NUEVO) --}}
                @if(isset($cobrosRealizados) && count($cobrosRealizados) > 0)
                <div class="card card-secondary">
                    <div class="card-header">
                        <h3 class="card-title"><i class="fas fa-history mr-1"></i> Historial de Cobros</h3>
                    </div>
                    <div class="card-body p-0">
                        <table class="table table-sm table-striped">
                            <thead>
                                <tr>
                                    <th>Fecha</th>
                                    <th>Voucher</th>
                                    <th>Método</th>
                                    <th class="text-right">Monto</th>
                                    <th class="text-center">Estado</th>
                                    <th class="text-center">Acción</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($cobrosRealizados as $cobro)
                                    <tr>
                                        <td>{{ \Carbon\Carbon::parse($cobro->cobro_fecha)->format('d/m/Y') }}</td>
                                        <td>{{ $cobro->nro_voucher ?? '-' }}</td>
                                        <td>{{ $cobro->metodo }}</td>
                                        <td class="text-right">{{ number_format($cobro->cobro_importe, 0, ',', '.') }}</td>
                                        <td class="text-center">
                                            @if($cobro->cobro_estado == 'COBRADO')
                                                <span class="badge badge-success">COBRADO</span>
                                            @else
                                                <span class="badge badge-secondary">ANULADO</span>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            @if($cobro->cobro_estado == 'COBRADO')
                                                <form action="{{ route('cuentasacobrar.anularCobro', $cobro->id_cobro) }}" method="POST" style="display:inline;">
                                                    @csrf
                                                    <button type="submit" class="btn btn-outline-danger btn-xs" 
                                                            onclick="return confirm('ATENCIÓN: ¿Está seguro de anular este cobro? El saldo de la cuenta aumentará.')"
                                                            title="Anular Cobro">
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
        // Inicializar Select2
        $('.select2').select2();

        // Agregar fila de pago
        $('#btn-agregar-pago').click(function() {
            var fila = `
            <div class="row item-pago mb-2">
                <div class="col-md-4">
                    <select name="forma_pago[]" class="form-control select2-new" required>
                        @foreach($metodos_pago as $id => $metodo)
                            <option value="{{ $id }}">{{ $metodo }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <input type="text" name="nro_voucher[]" class="form-control" placeholder="Nro Ref/Voucher">
                </div>
                <div class="col-md-4">
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

        // Eliminar fila
        $(document).on('click', '.btn-remove-pago', function() {
            $(this).closest('.item-pago').remove();
            calcularTotal();
        });
    });

    function calcularTotal() {
        let total = 0;
        $('.importe-input').each(function() {
            let val = $(this).val().replace(/\./g, '');
            if(val !== '') total += parseInt(val);
        });

        $('#display_total').text('Gs. ' + total.toLocaleString('es-PY'));

        // Validación visual en cliente
        let saldo = parseInt($('#saldo_pendiente').val());
        if(total > saldo) {
            $('#display_total').removeClass('text-success').addClass('text-danger');
            $('#btn-guardar').prop('disabled', true); // Bloqueo el botón
            Swal.fire('Atención', 'El monto supera el saldo pendiente', 'warning');
        } else {
            $('#display_total').removeClass('text-danger').addClass('text-success');
            $('#btn-guardar').prop('disabled', false);
        }
    }

    // Tu función format existente
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