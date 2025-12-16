<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Pagination\LengthAwarePaginator;
use RealRashid\SweetAlert\Facades\Alert;
use Luecano\NumeroALetras\NumeroALetras;

class VentaController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('permission:ventas index')->only(['index']);
        $this->middleware('permission:ventas create')->only(['create', 'store']);
        $this->middleware('permission:ventas edit')->only(['edit', 'update']);
        $this->middleware('permission:ventas destroy')->only(['anularVenta']);
    }

    public function index(Request $request)
    {
        $buscar = $request->get('buscar');

        if ($buscar) {
            $ventas = DB::select(
                "SELECT v.*, concat(c.clie_nombre,' ', c.clie_apellido) as cliente, c.clie_ci,
                users.name as usuario
                FROM ventas v
                    JOIN clientes c ON v.id_cliente = c.id_cliente
                    JOIN users ON v.user_id = users.id
                WHERE (CAST(v.id_venta AS TEXT) iLIKE ? OR CAST(c.clie_nombre AS TEXT) 
                iLIKE ? OR CAST(c.clie_apellido AS TEXT) iLIKE ? OR CAST(v.factura_nro AS TEXT) iLIKE ?
                OR CAST(c.clie_ci AS TEXT) iLIKE ?)
                order by v.fecha_venta desc",
                ['%' . $buscar . '%', '%' . $buscar . '%', '%' . $buscar . '%', '%' . $buscar . '%', '%' . $buscar . '%']
            );
        } else {
            $ventas = DB::select(
                "SELECT v.*, concat(c.clie_nombre,' ', c.clie_apellido) as cliente, c.clie_ci,
                users.name as usuario
                FROM ventas v
                    JOIN clientes c ON v.id_cliente = c.id_cliente
                    JOIN users ON v.user_id = users.id
                order by v.fecha_venta desc"
            );
        }

        $page = $request->input('page', 1);
        $perPage = 10;
        $total = count($ventas);
        $items = array_slice($ventas, ($page - 1) * $perPage, $perPage);
        $ventas = new LengthAwarePaginator($items, $total, $perPage, $page, [
            'path'  => $request->url(),
            'query' => $request->query(),
        ]);

        if ($request->ajax()) {
            return view('ventas.table')->with('ventas', $ventas);
        }

        $caja = DB::table('cajas')
            ->where('id_sucursal', auth()->user()->id_sucursal)
            ->pluck('descripcion', 'id_caja');

        $caja_abierta = DB::selectOne(
            "SELECT * FROM apertura_cierre_cajas WHERE user_id = ? AND estado = 'ABIERTA'",
            [auth()->user()->id]
        );

        return view('ventas.index')->with('ventas', $ventas)
            ->with('cajas', $caja)
            ->with('caja_abierta', $caja_abierta);
    }

    public function create()
    {
        $clientes = DB::table('clientes')
            ->selectRaw("id_cliente, concat(clie_nombre,' ', clie_apellido) as cliente")
            ->pluck('cliente', 'id_cliente');

        $usuario = auth()->user()->name;
        $condicion_venta = ['CONTADO' => 'CONTADO', 'CREDITO' => 'CREDITO'];
        $intervalo_vencimiento = ['7' => '7 Días', '15' => '15 Días', '30' => '30 Días'];
        $sucursales = DB::table('sucursales')->where('id_sucursal', auth()->user()->id_sucursal)
            ->pluck('descripcion', 'id_sucursal');

        $apertura_caja = DB::selectOne(
            "SELECT ap.id_apertura, ap.fecha_apertura,
                lpad('1', 3, '0') as establecimiento,
                lpad(cast(c.punto_expedicion as text), 3, '0') as punto_expedicion,
                lpad(cast(coalesce(c.ultima_factura_impresa, 0) + 1 as text), 7, '0') as nro_factura
            FROM apertura_cierre_cajas ap
                JOIN cajas c on c.id_caja = ap.id_caja 
            WHERE ap.user_id = ? and ap.estado = 'ABIERTA'
            GROUP BY ap.id_apertura, ap.fecha_apertura, c.punto_expedicion, c.ultima_factura_impresa",
            [auth()->user()->id]
        );

        if (!empty($apertura_caja) && !Carbon::parse($apertura_caja->fecha_apertura)->isToday()) {
            Alert::toast('Debe cerrar la caja de una fecha anterior para poder realizar una venta', 'error');
            return redirect()->route('ventas.index');
        }

        return view('ventas.create', compact('clientes', 'usuario', 'condicion_venta', 'intervalo_vencimiento', 'sucursales', 'apertura_caja'));
    }

    public function store(Request $request)
    {
        $input = $request->all();

        if (!$request->has('codigo') || empty($input['codigo'])) {
            Alert::error('Error', 'Debe agregar al menos un producto a la venta.');
            return redirect()->back()->withInput();
        }

        if (isset($input['condicion_venta']) && $input['condicion_venta'] === 'CONTADO') {
            $input['intervalo'] = null;
            $input['cantidad_cuota'] = null;
        }

        $validacion = Validator::make($input, [
            'id_cliente' => 'required|exists:clientes,id_cliente',
            'condicion_venta' => 'required|in:CONTADO,CREDITO',
            'intervalo' => 'nullable|required_if:condicion_venta,CREDITO|in:7,15,30',
            'cantidad_cuota' => 'nullable|required_if:condicion_venta,CREDITO|integer|min:1',
            'fecha_venta' => 'required|date',
            'id_apertura' => 'required|exists:apertura_cierre_cajas,id_apertura',
            'id_sucursal' => 'required|exists:sucursales,id_sucursal',
            'factura_nro' => 'nullable|string|max:20',
        ]);

        if ($validacion->fails()) {
            return redirect()->back()->withErrors($validacion)->withInput();
        }

        DB::beginTransaction();
        try {
            // Verificar stock antes de insertar nada
            foreach ($input['codigo'] as $key => $codigo) {
                $cantidad_a_vender = (int) $input['cantidad'][$key];
                $stock_actual = DB::table('stocks')
                    ->where('id_producto', $codigo)
                    ->where('id_sucursal', $input['id_sucursal'])
                    ->value('cantidad');

                if ($stock_actual === null || $cantidad_a_vender > $stock_actual) {
                    $producto_nombre = DB::table('productos')->where('id_producto', $codigo)->value('descripcion');
                    throw new \Exception("Stock insuficiente para: '{$producto_nombre}'. Disponible: {$stock_actual}");
                }
            }

            // Insertar venta (cabecera)
            $venta_id = DB::table('ventas')->insertGetId([
                'id_cliente' => $input['id_cliente'],
                'condicion_venta' => $input['condicion_venta'],
                'intervalo' => $input['intervalo'] ?? 0,
                'cantidad_cuota' => $input['cantidad_cuota'] ?? 0,
                'fecha_venta' => $input['fecha_venta'],
                'factura_nro' => $input['factura_nro'] ?? '0',
                'user_id' => auth()->id(),
                'total' => 0, // Se actualizará al final
                'id_sucursal' => $input['id_sucursal'],
                'estado' => 'COMPLETADO',
                'id_apertura' => $input['id_apertura'],
            ], 'id_venta');

            $subtotal_calculado = 0;

            // Insertar detalles y actualizar stock
            foreach ($input['codigo'] as $key => $codigo) {
                $monto = (float) str_replace(['.', ','], ['', '.'], $input['precio'][$key]);
                $cantidad = (int) $input['cantidad'][$key];

                $subtotal_calculado += $monto * $cantidad;
                DB::table('detalle_ventas')->insert([
                    'id_venta' => $venta_id,
                    'id_producto' => $codigo,
                    'cantidad' => $cantidad,
                    'precio' => $monto
                ]);

                DB::table('stocks')
                    ->where('id_producto', $codigo)
                    ->where('id_sucursal', $input['id_sucursal'])
                    ->decrement('cantidad', $cantidad);
            }

            // Actualizar total calculado
            DB::table('ventas')->where('id_venta', $venta_id)->update(['total' => $subtotal_calculado]);

            // Si dejamos esto activo, se crean cuotas duplicadas.
            /*
            if ($input['condicion_venta'] === 'CREDITO') {
                $this->generarCuentasCobrar(
                    $venta_id,
                    $subtotal_calculado,
                    (int)$input['cantidad_cuota'],
                    $input['fecha_venta'],
                    (int)$input['intervalo'],
                    $input['id_cliente']
                );
            }
            */

            // Actualizar numeración de factura
            if (!empty($input['factura_nro'])) {
                $factura_nro = (int) explode('-', $input['factura_nro'])[2];
                DB::table('cajas')
                    ->where('id_caja', function ($query) use ($input) {
                        $query->select('id_caja')->from('apertura_cierre_cajas')->where('id_apertura', $input['id_apertura']);
                    })
                    ->update(['ultima_factura_impresa' => $factura_nro]);
            }

            DB::commit();
            Alert::toast('Venta registrada exitosamente.', 'success');
            return redirect()->route('ventas.index');

        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Error store venta: ' . $e->getMessage());
            Alert::error('Error', $e->getMessage());
            return redirect()->back()->withInput();
        }
    }

    public function edit($id)
    {
        // ** CORRECCIÓN: Usar variable $ventas (plural) para que coincida con la vista edit.blade.php **
        $ventas = DB::selectOne('SELECT * FROM ventas WHERE id_venta = ?', [$id]);

        if (empty($ventas)) {
            Alert::error('Error', 'Venta no encontrada');
            return redirect()->route('ventas.index');
        }

        $clientes = DB::table('clientes')
            ->selectRaw("id_cliente, concat(clie_nombre,' ', clie_apellido) as cliente")
            ->pluck('cliente', 'id_cliente');

        $usuario = auth()->user()->name;
        $condicion_venta = ['CONTADO' => 'CONTADO', 'CREDITO' => 'CREDITO'];
        $intervalo_vencimiento = ['7' => '7 Días', '15' => '15 Días', '30' => '30 Días'];
        $sucursales = DB::table('sucursales')->where('id_sucursal', auth()->user()->id_sucursal)
            ->pluck('descripcion', 'id_sucursal');

        $detalle_venta = DB::select(
            "SELECT dv.*, p.descripcion
            FROM detalle_ventas dv
                JOIN productos p ON dv.id_producto = p.id_producto
            WHERE dv.id_venta = ?",
            [$id]
        );

        // Pasamos $ventas a la vista
        return view('ventas.edit', compact('ventas', 'detalle_venta', 'clientes', 'condicion_venta', 'intervalo_vencimiento', 'usuario', 'sucursales'));
    }

    public function update(Request $request, $id)
    {
        $input = $request->all();

        $ventaOriginal = DB::table('ventas')->where('id_venta', $id)->first();

        if (!$ventaOriginal) {
            Alert::error('Error', 'Venta no encontrada');
            return redirect()->route('ventas.index');
        }

        if ($ventaOriginal->estado === 'ANULADO') {
            Alert::warning('Atención', 'No se puede editar una venta anulada.');
            return redirect()->route('ventas.index');
        }

        if (!$request->has('codigo') || empty($input['codigo'])) {
            Alert::error('Error', 'Debe haber al menos un producto en la venta.');
            return redirect()->back()->withInput();
        }

        if (isset($input['condicion_venta']) && $input['condicion_venta'] === 'CONTADO') {
            $input['intervalo'] = null;
            $input['cantidad_cuota'] = null;
        }

        $validacion = Validator::make($input, [
            'condicion_venta' => 'required|in:CONTADO,CREDITO',
            'intervalo' => 'nullable|required_if:condicion_venta,CREDITO|in:7,15,30',
            'cantidad_cuota' => 'nullable|required_if:condicion_venta,CREDITO|integer|min:1',
            'id_sucursal' => 'required|exists:sucursales,id_sucursal',
        ]);

        if ($validacion->fails()) {
            return redirect()->back()->withErrors($validacion)->withInput();
        }

        DB::beginTransaction();
        try {

            // 1. Revertir el stock de los productos que tenía la venta originalmente
            $detallesViejos = DB::table('detalle_ventas')->where('id_venta', $id)->get();
            foreach ($detallesViejos as $d) {
                DB::table('stocks')
                    ->where('id_producto', $d->id_producto)
                    ->where('id_sucursal', $ventaOriginal->id_sucursal)
                    ->increment('cantidad', $d->cantidad); // Devolvemos stock
            }

            // 2. Borrar los detalles viejos
            DB::table('detalle_ventas')->where('id_venta', $id)->delete();

            // 3. Insertar los nuevos detalles y recalcular total
            $nuevoTotal = 0;
            foreach ($input['codigo'] as $key => $codigo) {
                $monto = (float) str_replace(['.', ','], ['', '.'], $input['precio'][$key]);
                $cantidad = (int) $input['cantidad'][$key];

                // Verificar stock disponible (incluyendo lo que acabamos de devolver)
                $stockActual = DB::table('stocks')
                    ->where('id_producto', $codigo)
                    ->where('id_sucursal', $ventaOriginal->id_sucursal)
                    ->value('cantidad');

                if ($cantidad > $stockActual) {
                    throw new \Exception("Stock insuficiente tras edición. Disponible: {$stockActual}, Requerido: {$cantidad}");
                }

                $nuevoTotal += $monto * $cantidad;

                // Insertar detalle
                DB::table('detalle_ventas')->insert([
                    'id_venta' => $id,
                    'id_producto' => $codigo,
                    'cantidad' => $cantidad,
                    'precio' => $monto
                ]);

                // Descontar stock
                DB::table('stocks')
                    ->where('id_producto', $codigo)
                    ->where('id_sucursal', $ventaOriginal->id_sucursal)
                    ->decrement('cantidad', $cantidad);
            }

            // 4. Actualizar la cabecera de la venta
            DB::table('ventas')->where('id_venta', $id)->update([
                'id_cliente' => $input['id_cliente'],
                'condicion_venta' => $input['condicion_venta'],
                'intervalo' => $input['intervalo'] ?? 0,
                'cantidad_cuota' => $input['cantidad_cuota'] ?? 0,
                'fecha_venta' => $input['fecha_venta'],
                'total' => $nuevoTotal,
            ]);

            // 5. Manejo de Cuentas a Cobrar (Crédito)
            if ($input['condicion_venta'] === 'CREDITO') {
                // Verificar si ya se pagó alguna cuota
                $pagosExisten = DB::table('cuentas_a_cobrar')
                    ->where('id_venta', $id)
                    ->where('estado', '!=', 'PENDIENTE')
                    ->exists();
                
                if ($pagosExisten) {
                    throw new \Exception("No se pueden modificar los plazos porque ya existen cobros realizados.");
                }

                // Borrar cuotas viejas y generar nuevas
                DB::table('cuentas_a_cobrar')->where('id_venta', $id)->delete();
                $this->generarCuentasCobrar(
                    $id,
                    $nuevoTotal,
                    (int)$input['cantidad_cuota'],
                    $input['fecha_venta'],
                    (int)$input['intervalo'],
                    $input['id_cliente']
                );
            } else {
                // Si pasó a CONTADO, borrar las pendientes
                DB::table('cuentas_a_cobrar')
                    ->where('id_venta', $id)
                    ->where('estado', 'PENDIENTE')
                    ->delete();
            }

            DB::commit();
            Alert::toast('Venta actualizada con éxito.', 'success');
            return redirect()->route('ventas.index');

        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Error update venta: ' . $e->getMessage());
            Alert::error('Error', $e->getMessage());
            return redirect()->back()->withInput();
        }
    }

    public function anularVenta($id)
    {
        $venta = DB::table('ventas')->where('id_venta', $id)->first();

        if (empty($venta)) {
            Alert::error('Error', 'Venta no encontrada');
            return redirect()->route('ventas.index');
        }

        if ($venta->estado === 'ANULADO') {
            Alert::warning('Atención', 'Esta venta ya ha sido anulada anteriormente.');
            return redirect()->route('ventas.index');
        }

        DB::beginTransaction();
        try {
            DB::table('ventas')->where('id_venta', $id)->update(['estado' => 'ANULADO']);
            $detalles = DB::table('detalle_ventas')->where('id_venta', $id)->get();

            /** @var \stdClass $item */
            foreach ($detalles as $item) {
                DB::table('stocks')
                    ->where('id_producto', $item->id_producto)
                    ->where('id_sucursal', $venta->id_sucursal)
                    ->increment('cantidad', $item->cantidad);
            }

            // Anular cuotas pendientes si existen
            DB::table('cuentas_a_cobrar')
                ->where('id_venta', $id)
                ->where('estado', 'PENDIENTE')
                ->update(['estado' => 'ANULADO']);

            DB::commit();
            Alert::success('Éxito', 'Venta anulada exitosamente.');
        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Error anular venta: ' . $e->getMessage());
            Alert::error('Error', 'No se pudo anular la venta.');
        }

        return redirect()->route('ventas.index');
    }

    // --- HELPER PARA GENERAR CUOTAS ---
    private function generarCuentasCobrar($idVenta, $total, $cuotas, $fechaVenta, $diasIntervalo, $idCliente)
    {
        if ($cuotas <= 0) return;

        $montoCuota = round($total / $cuotas);
        $fechaVencimiento = Carbon::parse($fechaVenta);
        
        for ($i = 1; $i <= $cuotas; $i++) {
            $fechaVencimiento->addDays($diasIntervalo);
            
            DB::table('cuentas_a_cobrar')->insert([
                'id_cliente' => $idCliente,
                'id_venta' => $idVenta,
                'vencimiento' => $fechaVencimiento->copy(),
                'importe' => $montoCuota,
                'nro_cuota' => $i,
                'estado' => 'PENDIENTE'
            ]);
        }
    }

    public function show($id)
    {
        // 1. Obtener la venta (Cabecera)
        $venta = DB::selectOne(
            "SELECT v.*, concat(c.clie_nombre,' ', c.clie_apellido) as cliente, c.clie_ci,
            users.name as usuario
            FROM ventas v
                JOIN clientes c ON v.id_cliente = c.id_cliente
                JOIN users ON v.user_id = users.id
            WHERE v.id_venta = ?",
            [$id]
        );

        if (empty($venta)) {
            Alert::toast('Venta no encontrada', 'error');
            return redirect()->route('ventas.index');
        }

        // 2. Obtener los detalles
        $detalle_venta = DB::select(
            "SELECT dv.*, p.descripcion
            FROM detalle_ventas dv
                JOIN productos p ON dv.id_producto = p.id_producto
            WHERE dv.id_venta = ?",
            [$id]
        );

        // --- DEFINICIÓN DE VARIABLES FALTANTES PARA LA VISTA ---

        // 3. Clientes (Para el select de clientes)
        $clientes = DB::table('clientes')
            ->selectRaw("id_cliente, concat(clie_nombre,' ', clie_apellido) as cliente")
            ->pluck('cliente', 'id_cliente');

        // 4. Listas estáticas y Sucursales (Para los otros selects)
        $condicion_venta = ['CONTADO' => 'CONTADO', 'CREDITO' => 'CREDITO'];
        $intervalo_vencimiento = ['7' => '7 Días', '15' => '15 Días', '30' => '30 Días'];
        
        $sucursales = DB::table('sucursales')
            ->where('id_sucursal', auth()->user()->id_sucursal)
            ->pluck('descripcion', 'id_sucursal');

        // 5. Usuario (que ya tenías)
        $usuario = $venta->usuario;

        // 6. RETORNO COMPLETO
        // Nota: Enviamos 'ventas' (plural) porque tu vista usa $ventas->total en la línea 80
        return view('ventas.show')
            ->with('ventas', $venta) 
            ->with('detalle_venta', $detalle_venta)
            ->with('usuario', $usuario)
            ->with('clientes', $clientes)
            ->with('condicion_venta', $condicion_venta)
            ->with('intervalo_vencimiento', $intervalo_vencimiento)
            ->with('sucursales', $sucursales);
    }

    public function buscarProducto(Request $request)
    {
        $query   = $request->get('query');
        $cod_suc = $request->get('cod_suc');

        $productos = DB::table('productos as p')
            ->join('stocks as s', 'p.id_producto', '=', 's.id_producto')
            ->where('s.id_sucursal', $cod_suc)
            ->where('s.cantidad', '>', 0)
            ->where(function ($q) use ($query) {
                $q->where('p.descripcion', 'ILIKE', "%{$query}%")
                    ->orWhere(DB::raw("CAST(p.id_producto AS TEXT)"), 'ILIKE', "%{$query}%");
            })
            ->select('p.*', 's.cantidad', 's.id_sucursal')
            ->limit(20)
            ->get();

        return view('ventas.body_producto')->with('productos', $productos);
    }

    public function factura($id)
    {
        $venta = DB::selectOne(
            "SELECT v.*, concat(c.clie_nombre,' ', c.clie_apellido) as cliente, c.clie_ci,
            users.name as usuario, c.clie_direccion, c.clie_telefono
            FROM ventas v
                JOIN clientes c ON v.id_cliente = c.id_cliente
                JOIN users ON v.user_id = users.id
            WHERE v.id_venta = ?",
            [$id]
        );

        if (empty($venta)) {
            Alert::toast('Venta no encontrada', 'error');
            return redirect()->route('ventas.index');
        }

        $detalle_venta = DB::select(
            "SELECT dv.*, p.descripcion
            FROM detalle_ventas dv
                JOIN productos p ON dv.id_producto = p.id_producto
            WHERE dv.id_venta = ?",
            [$id]
        );

        $formateo = new NumeroALetras();
        $numero_a_letras = $formateo->toWords($venta->total);

        return view('ventas.factura', compact('venta', 'detalle_venta', 'numero_a_letras'));
    }
}