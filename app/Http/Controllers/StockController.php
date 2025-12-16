<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use RealRashid\SweetAlert\Facades\Alert;
use Carbon\Carbon;

class StockController extends Controller
{
    // Constructor con middlewares y permisos
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('permission:stocks index')->only(['index']);
        $this->middleware('permission:stocks create')->only(['create', 'store']);
        $this->middleware('permission:stocks edit')->only(['edit', 'update']);
        $this->middleware('permission:stocks destroy')->only(['destroy']);
    }
    
    /**
     * Listado principal de Stocks con filtros y paginación.
     */
    public function index(Request $request)
    {
        // 1. OBTENER TODOS LOS FILTROS
        $buscar = trim($request->get('buscar'));
        $filtro_sucursal = $request->get('id_sucursal');
        $filtro_producto = $request->get('id_producto');

        // 2. CONSTRUIR CONSULTA SQL BASE
        // IMPORTANTE: Agregamos 'p.estado as estado_producto' para usarlo en la vista (pintar gris/rojo)
        $baseQuery = 'SELECT s.*, p.descripcion as producto, p.estado as estado_producto, suc.descripcion as sucursal 
                      FROM stocks s 
                      JOIN productos p ON s.id_producto = p.id_producto 
                      JOIN sucursales suc on s.id_sucursal = suc.id_sucursal';
        
        $whereClauses = [];
        $bindings = [];

        // 3. AÑADIR FILTROS DINÁMICAMENTE
        if ($buscar) {
            $whereClauses[] = "(p.descripcion ILIKE ? OR suc.descripcion ILIKE ?)";
            $bindings[] = '%' . $buscar . '%';
            $bindings[] = '%' . $buscar . '%';
        }

        if ($filtro_sucursal) {
            $whereClauses[] = "s.id_sucursal = ?";
            $bindings[] = $filtro_sucursal;
        }

        if ($filtro_producto) {
            $whereClauses[] = "s.id_producto = ?";
            $bindings[] = $filtro_producto;
        }

        // 4. COMBINAR LA CONSULTA
        $sql = $baseQuery;
        if (!empty($whereClauses)) {
            $sql .= " WHERE " . implode(" AND ", $whereClauses);
        }
        $sql .= " ORDER BY s.id_stock DESC";

        // 5. EJECUTAR CONSULTA
        $stocksData = DB::select($sql, $bindings);

        // 6. PAGINACIÓN MANUAL
        $page = $request->input('page', 1);
        $perPage = 10;
        $total = count($stocksData);
        $items = array_slice($stocksData, ($page - 1) * $perPage, $perPage);

        $stocks = new LengthAwarePaginator(
            $items,
            $total,
            $perPage,
            $page,
            ['path' => $request->url(), 'query' => $request->query()]
        );
        // Mantener parámetros de búsqueda en la paginación
        //$stocks->appends($request->query()); // Nota: LengthAwarePaginator a veces requiere esto manual o pasar 'query' arriba.

        // 7. DATOS PARA LOS FILTROS (Solo activos para UX, o todos si prefieres historial)
        // Aquí mostramos todos para poder filtrar historial antiguo, pero ordenados.
        $sucursales = DB::table('sucursales')->orderBy('descripcion')->pluck('descripcion', 'id_sucursal');
        $productos = DB::table('productos')->orderBy('descripcion')->pluck('descripcion', 'id_producto');

        // 8. MANEJO DE AJAX
        if ($request->ajax()) {
            return view('stocks.table', compact('stocks'))->render();
        }

        // 9. RETORNAR VISTA
        return view('stocks.index', compact('stocks', 'sucursales', 'productos'));
    }

    /**
     * Muestra el formulario para crear un nuevo registro de stock (Ajuste).
     */
    public function create()
    {
        // Solo sucursales Activas
        $sucursales = DB::table('sucursales')
                        ->where('estado', true)
                        ->orderBy('descripcion')
                        ->pluck('descripcion', 'id_sucursal');

        // BLINDAJE 1: Solo permitir seleccionar productos ACTIVOS para ajuste
        $productos = DB::table('productos')
                       ->where('estado', true) 
                       ->orderBy('descripcion')
                       ->pluck('descripcion', 'id_producto');
        
        // Opciones para el tipo de ajuste
        $tipos_ajuste = [
            'ENTRADA' => 'Entrada (Aumentar Stock)',
            'SALIDA'  => 'Salida (Disminuir Stock)',
        ];

        return view('stocks.create', compact('sucursales', 'productos', 'tipos_ajuste'));
    }

    /**
     * Guarda un nuevo movimiento de stock (Ajuste manual).
     */
    public function store(Request $request)
    {
        // 1. Validaciones básicas
        $request->validate([
            'id_producto' => 'required|exists:productos,id_producto',
            'id_sucursal' => 'required|exists:sucursales,id_sucursal',
            'cantidad' => 'required|integer|min:1', 
            'tipo_movimiento' => 'required|in:ENTRADA,SALIDA',
            'observacion' => 'nullable|max:255',
        ]);

        $id_producto = $request->id_producto;
        $id_sucursal = $request->id_sucursal;
        $cantidad_ajustada = $request->cantidad;
        $tipo_movimiento = $request->tipo_movimiento;

        // BLINDAJE 2: Verificación de Seguridad en Backend
        // Si alguien intenta enviar un ID de producto inactivo por inspector de elementos o URL
        $productoInfo = DB::table('productos')->where('id_producto', $id_producto)->first();
        
        if (!$productoInfo || !$productoInfo->estado) {
            Alert::error('Operación Denegada', 'El producto seleccionado se encuentra INACTIVO. No se pueden realizar movimientos de stock.');
            return redirect()->back()->withInput();
        }

        // 2. Obtener/Crear el registro de stock base
        $stock = DB::table('stocks')
                    ->where('id_producto', $id_producto)
                    ->where('id_sucursal', $id_sucursal)
                    ->first();
        
        // Si no existe stock previo en esa sucursal, lo inicializamos en 0
        if (!$stock) {
            $id_stock = DB::table('stocks')->insertGetId([
                'id_producto' => $id_producto,
                'id_sucursal' => $id_sucursal,
                'cantidad' => 0,
            ], 'id_stock');
            $stock = DB::table('stocks')->where('id_stock', $id_stock)->first();
        }

        // 3. Iniciar Transacción para asegurar integridad
        DB::beginTransaction();
        try {
            // 3.1. Validar Stock Suficiente (Solo para salidas)
            if ($tipo_movimiento === 'SALIDA') {
                if ($stock->cantidad < $cantidad_ajustada) {
                    DB::rollBack();
                    Alert::error('Error', 'Stock insuficiente. Solo quedan ' . $stock->cantidad . ' unidades.');
                    return redirect()->back()->withInput();
                }
            }

            // 3.2. Registrar el MOVIMIENTO (Auditoría)
            DB::table('stock_movimientos')->insert([
                'id_stock' => $stock->id_stock,
                'user_id' => auth()->user()->id,
                'tipo_movimiento' => $tipo_movimiento,
                'cantidad_ajustada' => $cantidad_ajustada,
                'fecha_movimiento' => Carbon::now(), // Carbon gestiona la fecha/hora actual
                'observacion' => $request->observacion,
            ]);

            // 3.3. Calcular nuevo stock
            $nuevo_stock = $stock->cantidad;
            if ($tipo_movimiento === 'ENTRADA') {
                $nuevo_stock += $cantidad_ajustada;
            } else {
                $nuevo_stock -= $cantidad_ajustada;
            }

            // 3.4. Actualizar la tabla principal de stocks
            DB::table('stocks')->where('id_stock', $stock->id_stock)->update([
                'cantidad' => $nuevo_stock,
            ]);

            DB::commit();

            Alert::success('Éxito', 'Ajuste registrado. Nuevo Stock: ' . $nuevo_stock);
            return redirect()->route('stocks.index');

        } catch (\Exception $e) {
            DB::rollBack();
            \Illuminate\Support\Facades\Log::error("Error en ajuste de stock: " . $e->getMessage());
            Alert::error('Error', 'Ocurrió un error al procesar el ajuste.');
            return redirect()->back()->withInput();
        }
    }
    
    /**
     * Muestra el historial de movimientos de un stock.
     */
    public function historial($id)
    {
        $stock = DB::table('stocks as s')
            ->join('productos as p', 's.id_producto', '=', 'p.id_producto')
            ->join('sucursales as suc', 's.id_sucursal', '=', 'suc.id_sucursal')
            ->select('s.id_stock', 'p.descripcion as producto', 'suc.descripcion as sucursal', 's.cantidad as stock_actual')
            ->where('s.id_stock', $id)
            ->first();

        if (!$stock) {
            Alert::error('Error', 'Registro de stock no encontrado.');
            return redirect()->route('stocks.index');
        }
        
        try {
             $movimientos = DB::table('stock_movimientos as m')
                ->leftJoin('users as u', 'm.user_id', '=', 'u.id') 
                ->select('m.*', DB::raw("COALESCE(u.name, 'Usuario Eliminado') as usuario"))
                ->where('m.id_stock', $id)
                ->orderBy('m.fecha_movimiento', 'DESC')
                ->get();

        } catch (\Exception $e) {
            // Fallback por seguridad
            $movimientos = DB::table('stock_movimientos as m')
                ->select('m.*', DB::raw("'Desconocido' as usuario")) 
                ->where('m.id_stock', $id)
                ->orderBy('m.fecha_movimiento', 'DESC')
                ->get();
        }

        return view('stocks.historial', compact('stock', 'movimientos'));
    }

    /**
     * Edición directa (Solo para casos de emergencia/admin).
     * También agregamos la validación de estado por seguridad.
     */
    public function edit($id)
    {
        $stock = DB::table('stocks as s')
            ->join('productos as p', 's.id_producto', '=', 'p.id_producto')
            ->join('sucursales as suc', 's.id_sucursal', '=', 'suc.id_sucursal')
            ->select('s.*', 'p.descripcion as producto', 'p.estado as estado_producto', 'suc.descripcion as sucursal')
            ->where('s.id_stock', $id)
            ->first();

        if (!$stock) {
            Alert::error('Error', 'Registro no encontrado.');
            return redirect()->route('stocks.index');
        }

        // Blindaje en Edición
        if (!$stock->estado_producto) {
            Alert::warning('Atención', 'No se puede editar el stock de un producto inactivo.');
            return redirect()->route('stocks.index');
        }
        
        return view('stocks.edit', compact('stock'));
    }

    public function update(Request $request, $id)
    {
        // Verificación previa del producto
        $stockCheck = DB::table('stocks')
                        ->join('productos', 'stocks.id_producto', '=', 'productos.id_producto')
                        ->where('stocks.id_stock', $id)
                        ->select('productos.estado')
                        ->first();

        if ($stockCheck && !$stockCheck->estado) {
             Alert::error('Error', 'Producto inactivo. No se permiten cambios.');
             return redirect()->route('stocks.index');
        }

        $request->validate([
            'cantidad' => 'required|integer|min:0',
        ]);
        
        DB::table('stocks')->where('id_stock', $id)->update([
            'cantidad' => $request->cantidad
        ]);

        Alert::success('Éxito', 'Stock actualizado correctamente.');
        return redirect()->route('stocks.index');
    }

    public function destroy($id)
    {
        try {
            // Verificar dependencias antes de borrar (movimientos)
            $tieneMovimientos = DB::table('stock_movimientos')->where('id_stock', $id)->exists();
            
            if($tieneMovimientos){
                Alert::error('No se puede eliminar', 'Existen movimientos históricos asociados a este registro.');
                return redirect()->route('stocks.index');
            }

            DB::table('stocks')->where('id_stock', $id)->delete();
            Alert::success('Éxito', 'Registro de stock eliminado.');

        } catch (\Exception $e) {
            Alert::error('Error', 'No se pudo eliminar el registro.');
        }
        
        return redirect()->route('stocks.index');
    }
}