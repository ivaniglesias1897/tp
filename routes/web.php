<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CargoController;
use App\Http\Controllers\CajaController;
use App\Http\Controllers\CiudadController;
use App\Http\Controllers\ClienteController;
use App\Http\Controllers\ComprasController;
use App\Http\Controllers\DepartamentoController;
use App\Http\Controllers\MarcaController;
use App\Http\Controllers\PedidosController;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\ProductoController;
use App\Http\Controllers\ProveedorController;
use App\Http\Controllers\ReporteController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\StockController;
use App\Http\Controllers\SucursalController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\VentaController;
use App\Http\Controllers\AperturaCierreCajaController;
use App\Http\Controllers\CobroController;
use App\Http\Controllers\CuentasACobrarController;
use App\Http\Controllers\CuentasAPagarController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\AuditoriaController;

// --- RUTAS PÚBLICAS ---
Route::get('/', function () {
    return view('auth.login');
});

Auth::routes();

Route::get('/home', [HomeController::class, 'index'])->name('home');


// --- RUTAS PROTEGIDAS ---
// Todas las rutas dentro de este grupo requerirán que el usuario haya iniciado sesión.
Route::middleware(['auth'])->group(function () {

    // --- MÓDULOS DE GESTIÓN (Recursos CRUD) ---
    Route::resource('cargos', CargoController::class);
    Route::resource('departamentos', DepartamentoController::class);
    Route::resource('proveedores', ProveedorController::class);
    Route::resource('ciudades', CiudadController::class);
    Route::resource('sucursales', SucursalController::class);
    Route::resource('marcas', MarcaController::class);
    Route::resource('productos', ProductoController::class);
    Route::resource('clientes', ClienteController::class);
    Route::resource('pedidos', PedidosController::class);
    Route::resource('compras', ComprasController::class);
    Route::resource('stocks', StockController::class);
    Route::resource('cobros', CobroController::class);
    Route::resource('cuentasacobrar', CuentasACobrarController::class);
    Route::resource('cuentasapagar', CuentasAPagarController::class);
    // Rutas para Cobros de Cuentas a Cobrar
    Route::get('cuentasacobrar/{id}/cobrar', [CuentasACobrarController::class, 'cobrar'])->name('cuentasacobrar.cobrar');
    Route::post('cuentasacobrar/{id}/guardar', [CuentasACobrarController::class, 'guardarCobro'])->name('cuentasacobrar.guardar');
    Route::post('cuentasacobrar/anular-cobro/{id_cobro}', [CuentasACobrarController::class, 'anularCobro'])->name('cuentasacobrar.anularCobro');
    // Rutas para Pagos de Cuentas a Pagar
    Route::get('cuentasapagar/{id}/pagar', [CuentasAPagarController::class, 'pagar'])->name('cuentasapagar.pagar');
    Route::post('cuentasapagar/{id}/guardarPago', [CuentasAPagarController::class, 'guardarPago'])->name('cuentasapagar.guardarPago');
    Route::post('cuentasapagar/anular-pago/{id_pago}', [CuentasAPagarController::class, 'anularPago'])->name('cuentasapagar.anularPago');

    Route::resource('stocks', StockController::class)->except(['edit', 'update', 'destroy']);
    Route::get('stocks/{id}/historial', [StockController::class, 'historial'])->name('stocks.historial');
    
    // Rutas de Auditoría
    Route::resource('auditoria', AuditoriaController::class);
    // Ruta específica para el historial visual (Timeline)
    Route::get('/auditoria/historial/{tabla}/{id}', [AuditoriaController::class, 'getHistorial']);

    // --- SEGURIDAD Y GESTIÓN DE USUARIOS ---
    Route::resource('users', UserController::class);
    Route::resource('permissions', PermissionController::class);
    Route::resource('roles', RoleController::class);
    Route::get('user/perfil', [UserController::class, 'perfil'])->name('user.perfil');
    Route::post('users/perfil/cambiar-password', [UserController::class, 'cambiarPassword'])->name('user.cambiarPassword');
    
    
    // --- GESTIÓN DE CAJA ---
    Route::resource('cajas', CajaController::class);
    Route::resource('apertura-cierre-caja', AperturaCierreCajaController::class);
    Route::get('apertura_cierre/editCierre/{id}', [AperturaCierreCajaController::class, 'editCierre'])->name('caja.editCierre');
    Route::get('apertura_cierre/cerrar_caja/{id}', [AperturaCierreCajaController::class, 'cerrar_caja'])->name('caja.cerrar');

    // --- RUTAS ESPECÍFICAS DE VENTAS
    Route::resource('ventas', VentaController::class)->except(['destroy']);
    Route::post('ventas/{id}/anular', [VentaController::class, 'anularVenta'])->name('ventas.anular');
    Route::get('imprimir-factura/{id}', [VentaController::class, 'factura'])->name('ventas.factura');

    // --- RUTAS DE BÚSQUEDA (AJAX) ---
    Route::get('buscar-productos', [VentaController::class, 'buscarProducto'])->name('ventas.buscarProducto');
    Route::get('buscar-productoscompras', [ComprasController::class, 'buscarProducto'])->name('compras.buscarProducto');

    // --- REPORTES ---
    // ** MEJORA: Se añaden nombres a todas las rutas de reportes para consistencia **
    Route::get('reporte-cargos', [ReporteController::class, 'rpt_cargos'])->name('reportes.cargos');
    Route::get('reporte-clientes', [ReporteController::class, 'rpt_clientes'])->name('reportes.clientes');
    Route::get('reporte-proveedores', [ReporteController::class, 'rpt_proveedores'])->name('reportes.proveedores');
    Route::get('reporte-productos', [ReporteController::class, 'rpt_productos'])->name('reportes.productos');
    Route::get('reporte-sucursales', [ReporteController::class, 'rpt_sucursales'])->name('reportes.sucursales');
    Route::get('reporte-ventas', [ReporteController::class, 'rpt_ventas'])->name('reportes.ventas');
    Route::get('reporte-compras', [ReporteController::class, 'rpt_compras'])->name('reportes.compras');
    
});