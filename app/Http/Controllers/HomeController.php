<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class HomeController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        Carbon::setLocale('es');
        $mesActual = Carbon::now()->month;
        $anioActual = Carbon::now()->year;
        $mesEnEspanol = Carbon::now()->translatedFormat('F Y');

        // 1. Total Ventas
        $totalVentasMes = DB::selectOne("
            SELECT COALESCE(SUM(total), 0) as total_ventas, COALESCE(COUNT(*), 0) as cantidad_ventas
            FROM ventas 
            WHERE EXTRACT(MONTH FROM fecha_venta) = ? AND EXTRACT(YEAR FROM fecha_venta) = ?
        ", [$mesActual, $anioActual]);

        // 2. Total Compras (Costos)
        $totalComprasMes = DB::selectOne("
            SELECT COALESCE(SUM(total), 0) as total_compras, COALESCE(COUNT(*), 0) as cantidad_compras
            FROM compras 
            WHERE EXTRACT(MONTH FROM fecha_compra) = ? AND EXTRACT(YEAR FROM fecha_compra) = ?
        ", [$mesActual, $anioActual]);

        // Cálculo de Ganancia Estimada
        // Nota: En un sistema real sería (Ventas - Costo de Ventas)
        $gananciaEstimada = $totalVentasMes->total_ventas - $totalComprasMes->total_compras;

        // Total Deuda por Cobrar 
        $deudaPorCobrar = DB::selectOne("
            SELECT COALESCE(SUM(importe), 0) as total_pendiente
            FROM cuentas_a_cobrar
            WHERE estado = 'PENDIENTE'
        ");

        // 3. Stock
        $totalStock = DB::selectOne("
            SELECT COALESCE(SUM(cantidad), 0) as total_stock,
                   COALESCE(COUNT(DISTINCT id_producto), 0) as productos_diferentes
            FROM stocks
        ");

        // 4. Ventas diarias para el gráfico
        $ventasPorDia = DB::select("
            SELECT DATE(fecha_venta) as fecha, SUM(total) as total_dia, COUNT(*) as cantidad_ventas_dia
            FROM ventas 
            WHERE EXTRACT(MONTH FROM fecha_venta) = ? AND EXTRACT(YEAR FROM fecha_venta) = ?
            GROUP BY DATE(fecha_venta)
            ORDER BY fecha_venta ASC
        ", [$mesActual, $anioActual]);

        // 5. Productos Bajo Stock (Top 5 críticos)
        $productosBajoStock = DB::select("
            SELECT p.descripcion as producto, COALESCE(SUM(s.cantidad), 0) as stock_total
            FROM productos p
            LEFT JOIN stocks s ON p.id_producto = s.id_producto
            GROUP BY p.id_producto, p.descripcion
            HAVING COALESCE(SUM(s.cantidad), 0) < 10
            ORDER BY stock_total ASC
            LIMIT 5
        ");

        // 6. Top 5 Más Vendidos
        $productosMasVendidos = DB::select("
            SELECT p.descripcion as producto, SUM(dv.cantidad) as cantidad_vendida, SUM(dv.cantidad * dv.precio) as total_vendido
            FROM detalle_ventas dv
            JOIN productos p ON dv.id_producto = p.id_producto
            JOIN ventas v ON dv.id_venta = v.id_venta
            WHERE EXTRACT(MONTH FROM v.fecha_venta) = ? AND EXTRACT(YEAR FROM v.fecha_venta) = ?
            GROUP BY p.id_producto, p.descripcion
            ORDER BY cantidad_vendida DESC
            LIMIT 5
        ", [$mesActual, $anioActual]);

        // Preparar arrays para Chart.js
        $fechasGrafico = [];
        $montosGrafico = [];
        foreach ($ventasPorDia as $venta) {
            $fechasGrafico[] = Carbon::parse($venta->fecha)->format('d/m'); // Formato día/mes
            $montosGrafico[] = floatval($venta->total_dia);
        }

        return view('home', compact(
            'totalVentasMes', 'totalComprasMes', 'totalStock', 'ventasPorDia',
            'productosBajoStock', 'productosMasVendidos', 'fechasGrafico', 'montosGrafico',
            'mesEnEspanol', 'gananciaEstimada', 'deudaPorCobrar'
        ));
    }
}