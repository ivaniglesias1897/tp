@extends('layouts.app')

@section('content')
<section class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-12">
                <h1>Historial de Movimientos de Stock</h1>
                <p>
                    Producto: <strong>{{ $stock->producto }}</strong> | 
                    Sucursal: <strong>{{ $stock->sucursal }}</strong> | 
                    Stock Actual: <span class="badge bg-primary">{{ $stock->stock_actual }}</span>
                </p>
            </div>
        </div>
    </div>
</section>

<div class="content px-3">
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Movimientos Registrados</h3>
            <div class="card-tools">
                 <a href="{{ route('stocks.index') }}" class="btn btn-default btn-sm">
                     <i class="fas fa-arrow-left"></i> Volver al Listado
                 </a>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th># Mov.</th>
                            <th>Tipo</th>
                            <th>Cantidad</th>
                            <th>Observación</th>
                            <th>Usuario</th>
                            <th>Fecha</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($movimientos as $mov)
                        <tr>
                            <td>{{ $mov->id_movimiento }}</td>
                            <td>
                                <span class="badge bg-{{ $mov->tipo_movimiento == 'ENTRADA' ? 'success' : 'danger' }}">
                                    {{ $mov->tipo_movimiento }}
                                </span>
                            </td>
                            <td>{{ $mov->cantidad_ajustada }}</td>
                            <td>{{ $mov->observacion ?? 'N/A' }}</td>
                            <td>{{ $mov->usuario }}</td>
                            <td>{{ \Carbon\Carbon::parse($mov->fecha_movimiento)->format('d/m/Y H:i') }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center">No hay movimientos registrados para este producto/sucursal.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection