<div class="card-body p-0">
    <div class="table-responsive">
        <table class="table" id="productos-table">
            <thead>
                <tr>
                    <th class="text-center">Nro. Cuenta</th>
                    <th class="text-center">Cliente</th>
                    <th class="text-center">N° de Factura</th>
                    <th class="text-center">Fecha de Venta</th>
                    <th class="text-center">Monto Original</th>
                    <th class="text-center">Saldo Pendiente</th>
                    <th class="text-center">Estado</th>
                    <th class="text-center">Vencimiento</th>
                    <th class="text-center">Cuota</th>
                    <th class="text-center">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($cuentasacobrar as $fila)
                    <tr>
                        <td class="text-center">{{ $fila->id_cta }}</td>
                        <td class="text-center">{{ $fila->cliente }}</td>
                        <td class="text-center">{{ $fila->factura_nro }}</td>
                        <td class="text-center">{{ \Carbon\Carbon::parse($fila->fecha_venta)->format('d/m/Y') }}</td>
                        
                        {{-- Monto Original --}}
                        <td class="text-center">{{ number_format($fila->importe, 0, ',', '.') }}</td>
                        
                        {{-- Saldo Pendiente --}}
                        <td class="text-center font-weight-bold text-danger">
                            {{ number_format($fila->saldo ?? $fila->importe, 0, ',', '.') }}
                        </td>

                        <td class="text-center">
                            @if ($fila->estado == 'PENDIENTE')
                                <span class="badge badge-warning">PENDIENTE</span>
                            @elseif ($fila->estado == 'PAGADO')
                                <span class="badge badge-success">PAGADO</span>
                            @else
                                <span class="badge badge-danger">{{ $fila->estado }}</span>
                            @endif
                        </td>
                        <td class="text-center">{{ \Carbon\Carbon::parse($fila->vencimiento)->format('d/m/Y') }}</td>
                        <td class="text-center">{{ $fila->nro_cuotas }}</td>
                        <td class="text-center">
                            {{-- LÓGICA DE BOTONES --}}
                            @if ($fila->estado != 'PAGADO')
                                {{-- Si debe plata: Botón Verde de Cobrar --}}
                                <a href="{{ route('cuentasacobrar.cobrar', $fila->id_cta) }}" 
                                   class="btn btn-success btn-xs" 
                                   title="Registrar Cobro">
                                    <i class="fas fa-money-bill-wave"></i> Cobrar
                                </a>
                            @else
                                {{-- Si ya pagó: Botón Azul para Ver Historial (y poder anular) --}}
                                <a href="{{ route('cuentasacobrar.cobrar', $fila->id_cta) }}" 
                                   class="btn btn-info btn-xs" 
                                   title="Ver Historial / Anular">
                                    <i class="fas fa-eye"></i> Ver
                                </a>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="10" class="text-center">No se encontraron cuentas a cobrar.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="card-footer clearfix">
        <div class="float-right">
            @if($cuentasacobrar->count() > 0)
                {{ $cuentasacobrar->links() }}
            @endif
        </div>
    </div>
</div>