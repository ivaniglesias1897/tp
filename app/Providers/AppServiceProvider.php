<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\DB;
use Illuminate\Pagination\Paginator;
use Carbon\Carbon;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Usar Bootstrap para la paginación
        Paginator::useBootstrap();

        // --- LÓGICA DE ALERTAS GLOBALES ---
        // Esto ejecuta las consultas SOLO cuando se carga el layout principal
        View::composer('layouts.app', function ($view) {
            
            // 1. Alerta de Stock Bajo (Menos de 10 unidades)
            $alertasStock = DB::select("
                SELECT p.descripcion, SUM(s.cantidad) as total
                FROM productos p
                JOIN stocks s ON p.id_producto = s.id_producto
                GROUP BY p.id_producto, p.descripcion
                HAVING SUM(s.cantidad) < 10
            ");

            // 2. Alerta de Cuentas por Cobrar (Vencen en los próximos 3 días o ya vencieron)
            // Usamos NOW() y NOW() + 3 días
            $alertasVencimientos = DB::select("
                SELECT c.clie_nombre || ' ' || c.clie_apellido as cliente, 
                       ca.vencimiento, 
                       ca.importe
                FROM cuentas_a_cobrar ca
                JOIN clientes c ON ca.id_cliente = c.id_cliente
                WHERE ca.estado = 'PENDIENTE'
                AND ca.vencimiento <= (CURRENT_DATE + INTERVAL '3 days')
                ORDER BY ca.vencimiento ASC
            ");

            // Calcular total de notificaciones
            $totalNotificaciones = count($alertasStock) + count($alertasVencimientos);

            // Compartir variables con la vista
            $view->with('alertasStock', $alertasStock)
                 ->with('alertasVencimientos', $alertasVencimientos)
                 ->with('totalNotificaciones', $totalNotificaciones);
        });
    }
}