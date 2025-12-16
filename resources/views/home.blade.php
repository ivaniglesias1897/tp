@extends('layouts.app')

@push('styles')
    <style>
        .card-gradient-primary {
            background: linear-gradient(45deg, #4e73df, #224abe);
            color: white;
        }

        .card-gradient-success {
            background: linear-gradient(45deg, #1cc88a, #13855c);
            color: white;
        }

        .card-gradient-info {
            background: linear-gradient(45deg, #36b9cc, #258391);
            color: white;
        }

        .card-gradient-warning {
            background: linear-gradient(45deg, #f6c23e, #dda20a);
            color: white;
        }

        /* Texto blanco en tarjetas con fondo oscuro */
        .card-gradient-primary .text-gray-800,
        .card-gradient-success .text-gray-800,
        .card-gradient-info .text-gray-800,
        .card-gradient-warning .text-gray-800 {
            color: white !important;
        }

        .card-gradient-primary .text-muted,
        .card-gradient-success .text-muted,
        .card-gradient-info .text-muted,
        .card-gradient-warning .text-muted {
            color: rgba(255, 255, 255, 0.7) !important;
        }

        /* Iconos gigantes de fondo */
        .icon-bg {
            position: absolute;
            right: 15px;
            top: 15px;
            font-size: 4rem;
            color: rgba(255, 255, 255, 0.15);
            /* Transparente */
            z-index: 0;
        }

        .card-body-content {
            position: relative;
            z-index: 1;
            /* Asegura que el texto esté sobre el icono */
        }

        .chart-area {
            position: relative;
            height: 350px;
            /* Un poco más alto */
        }

        /* Títulos de sección */
        .dashboard-title {
            font-weight: 800;
            color: #4e73df;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
    </style>
@endpush

@section('content')
    <div class="container-fluid pt-3">

        <!-- Encabezado -->
        <div class="d-sm-flex align-items-center justify-content-between mb-4">
            <div>
                <h1 class="h3 mb-0 text-gray-800 dashboard-title">Dashboard</h1>
                <p class="mb-0 text-muted">Resumen financiero de <b class="text-dark">{{ $mesEnEspanol }}</b></p>
            </div>
            <a href="#" class="d-none d-sm-inline-block btn btn-sm btn-primary shadow-sm" onclick="window.print()">
                <i class="fas fa-download fa-sm text-white-50"></i> Generar Reporte
            </a>
        </div>

        <!-- Fila de Tarjetas (Widgets) -->
        <div class="row">

            <!-- 1. Ventas (Azul) -->
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card card-gradient-primary shadow h-100 py-2 border-0">
                    <div class="card-body">
                        <div class="card-body-content">
                            <div class="text-xs font-weight-bold text-uppercase mb-1" style="opacity: 0.8">
                                Ventas (Mensual)</div>
                            <div class="h3 mb-0 font-weight-bold text-white">
                                Gs. {{ number_format($totalVentasMes->total_ventas, 0, ',', '.') }}
                            </div>
                            <div class="mt-2 text-xs">
                                <i class="fas fa-receipt mr-1"></i> {{ $totalVentasMes->cantidad_ventas }} transacciones
                            </div>
                        </div>
                        <i class="fas fa-shopping-bag icon-bg"></i>
                    </div>
                </div>
            </div>

            <!-- 2. Compras (Verde - Invertido conceptualmente para costos, o Rojo si prefieres alerta) -->
            <!-- Usamos Info (Celeste) para compras para diferenciar de Ganancia -->
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card card-gradient-info shadow h-100 py-2 border-0">
                    <div class="card-body">
                        <div class="card-body-content">
                            <div class="text-xs font-weight-bold text-uppercase mb-1" style="opacity: 0.8">
                                Compras (Costos)</div>
                            <div class="h3 mb-0 font-weight-bold text-white">
                                Gs. {{ number_format($totalComprasMes->total_compras, 0, ',', '.') }}
                            </div>
                            <div class="mt-2 text-xs">
                                <i class="fas fa-truck mr-1"></i> {{ $totalComprasMes->cantidad_compras }} ingresos
                            </div>
                        </div>
                        <i class="fas fa-truck-loading icon-bg"></i>
                    </div>
                </div>
            </div>

            <!-- 3. Ganancia Estimada (Verde - El dinero real) -->
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card card-gradient-success shadow h-100 py-2 border-0">
                    <div class="card-body">
                        <div class="card-body-content">
                            <div class="text-xs font-weight-bold text-uppercase mb-1" style="opacity: 0.8">
                                Ganancia Estimada</div>
                            <div class="h3 mb-0 font-weight-bold text-white">
                                Gs. {{ number_format($gananciaEstimada, 0, ',', '.') }}
                            </div>
                            <div class="mt-2 text-xs">
                                <i class="fas fa-chart-line mr-1"></i> Balance del mes
                            </div>
                        </div>
                        <i class="fas fa-wallet icon-bg"></i>
                    </div>
                </div>
            </div>

            <!-- 4. Deuda por Cobrar (Amarillo - Atención) -->
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card card-gradient-warning shadow h-100 py-2 border-0">
                    <div class="card-body">
                        <div class="card-body-content">
                            <div class="text-xs font-weight-bold text-uppercase mb-1" style="opacity: 0.8">
                                Por Cobrar (Pendiente)</div>
                            <div class="h3 mb-0 font-weight-bold text-white">
                                Gs. {{ number_format($deudaPorCobrar->total_pendiente, 0, ',', '.') }}
                            </div>
                            <div class="mt-2 text-xs">
                                <i class="fas fa-exclamation-circle mr-1"></i> Créditos activos
                            </div>
                        </div>
                        <i class="fas fa-hand-holding-usd icon-bg"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Fila Principal: Gráfico y Alertas -->
        <div class="row">

            <!-- Gráfico de Ventas (Ancho 8/12) -->
            <div class="col-xl-8 col-lg-7">
                <div class="card shadow mb-4">
                    <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between bg-white">
                        <h6 class="m-0 font-weight-bold text-primary">Evolución de Ventas Diarias</h6>
                    </div>
                    <div class="card-body">
                        <div class="chart-area">
                            <canvas id="ventasChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Alertas de Stock (Ancho 4/12) -->
            <div class="col-xl-4 col-lg-5">
                <div class="card shadow mb-4">
                    <div class="card-header py-3 bg-white">
                        <h6 class="m-0 font-weight-bold text-danger">
                            <i class="fas fa-bell mr-1"></i> Alertas de Stock Bajo
                        </h6>
                    </div>
                    <div class="card-body p-0">
                        @if (count($productosBajoStock) > 0)
                            <ul class="list-group list-group-flush">
                                @foreach ($productosBajoStock as $producto)
                                    <li class="list-group-item d-flex justify-content-between align-items-center">
                                        <div>
                                            <div class="font-weight-bold text-dark">
                                                {{ Str::limit($producto->producto, 25) }}</div>
                                            <small class="text-danger">Crítico</small>
                                        </div>
                                        <span class="badge badge-danger badge-pill" style="font-size: 1rem;">
                                            {{ $producto->stock_total }}
                                        </span>
                                    </li>
                                @endforeach
                            </ul>
                        @else
                            <div class="text-center p-4">
                                <i class="fas fa-check-circle text-success fa-3x mb-3"></i>
                                <p class="mb-0 text-gray-600">Todo el inventario está saludable.</p>
                            </div>
                        @endif
                        <div class="card-footer text-center">
                            <a href="{{ url('reporte-productos') }}" class="small font-weight-bold">Ver Inventario Completo
                                <i class="fas fa-arrow-right"></i></a>
                        </div>
                    </div>
                </div>

                <!-- Resumen Rápido -->
                <div class="card shadow mb-4">
                    <div class="card-header py-3 bg-white">
                        <h6 class="m-0 font-weight-bold text-primary">Top Producto del Mes</h6>
                    </div>
                    <div class="card-body text-center">
                        @if (count($productosMasVendidos) > 0)
                            <div class="h1 text-primary mb-1"><i class="fas fa-trophy"></i></div>
                            <h4 class="font-weight-bold">{{ Str::limit($productosMasVendidos[0]->producto, 20) }}</h4>
                            <p class="text-muted mb-0">
                                {{ $productosMasVendidos[0]->cantidad_vendida }} unidades vendidas
                            </p>
                        @else
                            <p class="text-muted">Sin datos aún</p>
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
        // Configuración Gráfico Moderno
        const ctx = document.getElementById('ventasChart').getContext('2d');

        // Gradiente para el gráfico
        let gradient = ctx.createLinearGradient(0, 0, 0, 400);
        gradient.addColorStop(0, 'rgba(78, 115, 223, 0.5)'); // Azul inicio
        gradient.addColorStop(1, 'rgba(78, 115, 223, 0.05)'); // Azul fin (casi transparente)

        const ventasChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: @json($fechasGrafico),
                datasets: [{
                    label: 'Ventas (Gs)',
                    data: @json($montosGrafico),
                    borderColor: '#4e73df',
                    backgroundColor: gradient,
                    pointBackgroundColor: '#4e73df',
                    pointBorderColor: '#fff',
                    pointHoverBackgroundColor: '#fff',
                    pointHoverBorderColor: '#4e73df',
                    fill: true,
                    tension: 0.3, // Curvas suaves
                    borderWidth: 3
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    }, // Ocultar leyenda (título ya está en la tarjeta)
                    tooltip: {
                        backgroundColor: "rgb(255,255,255)",
                        bodyColor: "#858796",
                        titleColor: "#6e707e",
                        borderColor: '#dddfeb',
                        borderWidth: 1,
                        padding: 10,
                        displayColors: false,
                        callbacks: {
                            label: function(context) {
                                return 'Gs. ' + context.parsed.y.toLocaleString('es-PY');
                            }
                        }
                    }
                },
                scales: {
                    x: {
                        grid: {
                            display: false,
                            drawBorder: false
                        },
                        ticks: {
                            maxTicksLimit: 7
                        }
                    },
                    y: {
                        ticks: {
                            maxTicksLimit: 5,
                            padding: 10,
                            callback: function(value) {
                                return 'Gs. ' + value.toLocaleString('es-PY');
                            }
                        },
                        grid: {
                            color: "rgb(234, 236, 244)",
                            zeroLineColor: "rgb(234, 236, 244)",
                            drawBorder: false,
                            borderDash: [2],
                            zeroLineBorderDash: [2]
                        }
                    }
                }
            }
        });
    </script>
@endpush
