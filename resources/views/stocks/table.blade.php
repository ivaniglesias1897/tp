<div class="card-body p-0">
    <div class="table-responsive">
        <table class="table table-hover" id="stocks-table">
            <thead>
                <tr>
                    <th class="text-center">#</th>
                    <th class="text-center">Producto</th>
                    {{-- Nueva columna para estado del producto --}}
                    <th class="text-center">Estado Prod.</th>
                    <th class="text-center">Sucursal</th>
                    <th class="text-center">Stock Actual</th>
                    <th class="text-center" style="width: 120px;">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse($stocks as $stock)
                    {{-- Fila con opacidad si el producto está inactivo --}}
                    <tr class="{{ !$stock->estado_producto ? 'text-muted bg-light' : '' }}">
                        <td class="text-center align-middle">{{ $stock->id_stock }}</td>
                        
                        <td class="text-center align-middle font-weight-bold">
                            {{ $stock->producto }}
                        </td>

                        {{-- CAMBIO VISUAL: Indicador de Estado del Producto --}}
                        <td class="text-center align-middle">
                            @if($stock->estado_producto)
                                <span class="badge badge-success">Activo</span>
                            @else
                                <span class="badge badge-secondary" title="Producto Inactivo - Movimientos Bloqueados">Inactivo</span>
                            @endif
                        </td>

                        <td class="text-center align-middle">{{ $stock->sucursal }}</td>
                        
                        <td class="text-center align-middle">
                            {{-- Si está inactivo, el stock se muestra en gris --}}
                            <span class="badge {{ $stock->estado_producto ? ($stock->cantidad > 0 ? 'bg-success' : 'bg-danger') : 'bg-secondary' }}" 
                                  style="font-size: 1.1em;">
                                {{ $stock->cantidad }}
                            </span>
                        </td>
                        
                        <td class="text-center align-middle">
                             <div class="btn-group">
                                {{-- El historial siempre se puede ver, incluso si está inactivo --}}
                                <a href="{{ route('stocks.historial', $stock->id_stock) }}" 
                                   class="btn btn-info btn-xs" 
                                   title="Ver Historial" 
                                   data-toggle="tooltip">
                                     <i class="fas fa-search-plus"></i>
                                </a>
                             </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center text-muted py-3">
                            <i class="fas fa-search mb-2"></i><br>
                            No se encontraron registros de stock.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="card-footer clearfix">
        <div class="float-right">
            @if($stocks->count() > 0)
                {{ $stocks->links() }}
            @endif
        </div>
    </div>
</div>