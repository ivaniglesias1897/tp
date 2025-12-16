@extends('layouts.app')

@push('styles')
<style>
    .border-left-primary {
        border-left: 0.25rem solid #4e73df !important;
    }

    .border-left-success {
        border-left: 0.25rem solid #1cc88a !important;
    }

    .border-left-info {
        border-left: 0.25rem solid #36b9cc !important;
    }

    .border-left-warning {
        border-left: 0.25rem solid #f6c23e !important;
    }

    .text-gray-800 {
        color: #5a5c69 !important;
    }

    .text-gray-300 {
        color: #dddfeb !important;
    }

    .shadow {
        box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15) !important;
    }

    .chart-area {
        position: relative;
        height: 300px;
    }

    .card {
        position: relative;
        display: flex;
        flex-direction: column;
        min-width: 0;
        word-wrap: break-word;
        background-color: #fff;
        background-clip: border-box;
        border: 1px solid #e3e6f0;
        border-radius: 0.35rem;
    }
</style>
@endpush

@section('content')
    <div class="container-fluid">
        <div class="row mb-4">
            <div class="col-12">
                <h1 class="text-primary mb-0">Dashboard</h1>
                <p class="text-muted">Resumen de actividades del mes de {{ $mes_actual }}</p>
            </div>
        </div> <!-- Cards de métricas principales -->
        <div class="row mb-4">
            <!-- Total Ventas del Mes -->
            <div class="col-md-3 mb-3">
                <div class="card border-left-primary shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                    Ventas del Mes
                                </div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">
                                    {{ number_format($totalVentasMes->cantidad_ventas, 0) }}
                                </div>
                                <div class="text-xs text-muted">
                                    Total: Gs.{{ number_format($totalVentasMes->total_ventas, 0, ',', '.') }}
                                </div>
                            </div>
                            <div class="col-auto">
                                <i class="fa fa-shopping-cart fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Total Compras del Mes -->
            <div class="col-md-3 mb-3">
                <div class="card border-left-success shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                    Compras del Mes
                                </div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">
                                    {{ number_format($totalComprasMes->cantidad_compras, 0) }}
                                </div>
                                <div class="text-xs text-muted">
                                    Total: Gs.{{ number_format($totalComprasMes->total_compras, 0, ',', '.') }}
                                </div>
                            </div>
                            <div class="col-auto">
                                <i class="fa fa-truck fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Stock Total -->
            <div class="col-md-3 mb-3">
                <div class="card border-left-info shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                    Stock Total
                                </div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">
                                    {{ number_format($totalStock->total_stock, 0) }}
                                </div>
                                <div class="text-xs text-muted">
                                    {{ $totalStock->productos_diferentes }} productos diferentes
                                </div>
                            </div>
                            <div class="col-auto">
                                <i class="fa fa-cube fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Productos Bajo Stock -->
            <div class="col-md-3 mb-3">
                <div class="card border-left-warning shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                    Bajo Stock
                                </div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">
                                    {{ count($productosBajoStock) }}
                                </div>
                                <div class="text-xs text-muted">
                                    Productos con stock crítico
                                </div>
                            </div>
                            <div class="col-auto">
                                <i class="fa fa-exclamation-triangle fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Gráficos y tablas -->
        <div class="row">
            <!-- Gráfico de Ventas -->
            <div class="col-xl-8 col-lg-7">
                <div class="card shadow mb-4">
                    <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                        <h6 class="m-0 font-weight-bold text-primary">Ventas Diarias del Mes</h6>
                    </div>
                    <div class="card-body">
                        <div class="chart-area">
                            <canvas id="ventasChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Productos con Bajo Stock -->
            <div class="col-xl-4 col-lg-5">
                <div class="card shadow mb-4">
                    <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                        <h6 class="m-0 font-weight-bold text-primary">Productos con Stock Crítico</h6>
                    </div>
                    <div class="card-body">
                        @if (count($productosBajoStock) > 0)
                            @foreach ($productosBajoStock as $producto)
                                <div class="d-flex align-items-center justify-content-between mb-2">
                                    <div class="small">
                                        {{ Str::limit($producto->producto, 25) }}
                                    </div>
                                    <div class="small font-weight-bold">
                                        <span class="badge badge-{{ $producto->stock_total == 0 ? 'danger' : 'warning' }}">
                                            {{ $producto->stock_total ?? 0 }}
                                        </span>
                                    </div>
                                </div>
                            @endforeach
                        @else
                            <div class="text-center text-muted">
                                <i class="fa fa-check-circle fa-3x mb-3"></i>
                                <p>Todos los productos tienen stock suficiente</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Segunda fila con productos más vendidos -->
        <div class="row">
            <!-- Productos Más Vendidos -->
            <div class="col-xl-6 col-lg-6">
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">Top 5 Productos Más Vendidos - {{ $mes_actual }}
                        </h6>
                    </div>
                    <div class="card-body">
                        @if (count($productosMasVendidos) > 0)
                            @foreach ($productosMasVendidos as $index => $producto)
                                <div class="d-flex align-items-center justify-content-between mb-3">
                                    <div class="d-flex align-items-center">
                                        <div class="mr-3">
                                            <span class="badge badge-primary rounded-circle"
                                                style="width: 30px; height: 30px; display: flex; align-items: center; justify-content: center;">
                                                {{ $index + 1 }}
                                            </span>
                                        </div>
                                        <div>
                                            <div class="small font-weight-bold">
                                                {{ Str::limit($producto->producto, 30) }}
                                            </div>
                                            <div class="text-xs text-muted">
                                                Total: Gs. {{ number_format($producto->total_vendido, 0, ',', '.') }}
                                            </div>
                                        </div>
                                    </div>
                                    <div class="text-right">
                                        <div class="small font-weight-bold">
                                            {{ $producto->cantidad_vendida }} unidades
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        @else
                            <div class="text-center text-muted">
                                <i class="fa fa-shopping-cart fa-3x mb-3"></i>
                                <p>No hay ventas registradas este mes</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Resumen Rápido -->
            <div class="col-xl-6 col-lg-6">
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">Resumen Rápido</h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-6">
                                <div class="text-center">
                                    <div class="h4 font-weight-bold text-primary">
                                        {{ count($ventasPorDia) }}
                                    </div>
                                    <div class="text-xs text-muted">Días con ventas</div>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="text-center">
                                    <div class="h4 font-weight-bold text-success">
                                        @if (count($ventasPorDia) > 0)
                                            Gs. {{ number_format(collect($ventasPorDia)->avg('total_dia'), 0, ',', '.') }}
                                        @else
                                            Gs. 0
                                        @endif
                                    </div>
                                    <div class="text-xs text-muted">Promedio por día</div>
                                </div>
                            </div>
                        </div>
                        <hr>
                        <div class="row">
                            <div class="col-6">
                                <div class="text-center">
                                    <div class="h4 font-weight-bold text-info">
                                        {{ count($productosBajoStock) }}
                                    </div>
                                    <div class="text-xs text-muted">Stock crítico</div>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="text-center">
                                    <div class="h4 font-weight-bold text-warning">
                                        {{ count($productosMasVendidos) }}
                                    </div>
                                    <div class="text-xs text-muted">Productos vendidos</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tabla de ventas recientes -->
        <div class="row">
            <div class="col-12">
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">Ventas por Día - {{ $mes_actual }}</h6>
                    </div>
                    <div class="card-body">
                        @if (count($ventasPorDia) > 0)
                            <div class="table-responsive">
                                <table class="table table-bordered" width="100%" cellspacing="0">
                                    <thead>
                                        <tr>
                                            <th>Fecha</th>
                                            <th>Cantidad de Ventas</th>
                                            <th>Total del Día</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($ventasPorDia as $venta)
                                            <tr>
                                                <td>{{ \Carbon\Carbon::parse($venta->fecha)->format('d/m/Y') }}</td>
                                                <td>{{ $venta->cantidad_ventas_dia }}</td>
                                                <td>Gs. {{ number_format($venta->total_dia, 0, ',', '.') }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <div class="text-center text-muted">
                                <i class="fa fa-line-chart fa-3x mb-3"></i>
                                <p>No hay ventas registradas este mes</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        // Configuración del gráfico de ventas
        const ctx = document.getElementById('ventasChart').getContext('2d');
        const ventasChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: @json($fechasGrafico),
                datasets: [{
                    label: 'Ventas Diarias',
                    data: @json($montosGrafico),
                    borderColor: 'rgb(75, 192, 192)',
                    backgroundColor: 'rgba(75, 192, 192, 0.2)',
                    tension: 0.1,
                    fill: true
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value, index, values) {
                                return 'Gs. ' + value.toLocaleString();
                            }
                        }
                    }
                },
                plugins: {
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return 'Ventas: Gs. ' + context.parsed.y.toLocaleString();
                            }
                        }
                    }
                }
            }
        });
    </script>
@endpush