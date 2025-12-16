<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Pagination\LengthAwarePaginator;
use RealRashid\SweetAlert\Facades\Alert;

class PedidosController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('permission:pedidos index')->only(['index']);
        $this->middleware('permission:pedidos create')->only(['create', 'store']);
        $this->middleware('permission:pedidos edit')->only(['edit', 'update']);
        $this->middleware('permission:pedidos destroy')->only(['destroy']);
    }
    /** LISTADO */
    public function index(Request $request)
    {
        $buscar = trim($request->get('buscar'));
        
        // 1. Consulta Base: Unimos con Cliente y Usuario para buscar por nombres/CI.
        // NOTA: Usamos LEFT JOIN para que los pedidos sin un usuario/cliente relacionado (aunque no debería pasar) no se pierdan.
        $sqlBase = "SELECT p.*, 
                           CONCAT(c.clie_nombre,' ', c.clie_apellido) AS cliente, 
                           c.clie_ci, 
                           u.name AS usuario
                    FROM pedidos p
                    JOIN clientes c ON p.id_cliente = c.id_cliente
                    JOIN users u ON p.id_usuario = u.id";
                    
        $sqlWhere = '';
        $bindings = [];
        $sqlOrder = 'ORDER BY p.fecha_pedido DESC';

        if (!empty($buscar)) {
            
            $like = '%' . $buscar . '%';
            
            // Definición de las condiciones de búsqueda
            // Usamos ::text para buscar en campos numéricos (como ID o Total) o fechas.
            $sqlWhere = "WHERE (
                p.id_pedido::text ILIKE ?  -- # Pedido
                OR c.clie_ci ILIKE ? -- CI/RUC del Cliente
                OR CONCAT(c.clie_nombre,' ', c.clie_apellido) ILIKE ? -- Nombre del Cliente
                OR p.total_pedido::text ILIKE ? -- Total Pedido
                OR u.name ILIKE ? -- Usuario (Vendedor)
                OR p.estado ILIKE ? -- Estado
                OR p.fecha_pedido::text ILIKE ? -- Fecha Pedido (buscando por fragmento de fecha)
            )";
            
            // 7 parámetros para los 7 signos de interrogación (?)
            $bindings = [
                $like, // p.id_pedido
                $like, // c.clie_ci
                $like, // CONCAT(c.clie_nombre,' ', c.clie_apellido)
                $like, // p.total_pedido
                $like, // u.name
                $like, // p.estado
                $like  // p.fecha_pedido
            ];
            
            // NOTA: No incluimos un caso especial para ID exacto como en Cajas, 
            // ya que la búsqueda general por ID::text es suficiente para Pedidos.
        }

        // 2. Ejecutar Consulta
        $pedidos = DB::select($sqlBase . ' ' . $sqlWhere . ' ' . $sqlOrder, $bindings);

        // 3. Paginación Manual (Copiando la lógica de CajaController)
        $page = $request->input('page', 1);
        $perPage = 10; // 10 registros por página
        $total = count($pedidos);
        $items = array_slice($pedidos, ($page - 1) * $perPage, $perPage);

        $pedidos = new LengthAwarePaginator(
            $items, 
            $total, 
            $perPage, 
            $page, 
            ['path' => $request->url(), 'query' => $request->query()]
        );
        
        // Adjuntamos parámetros para mantener la búsqueda en la paginación
        $pedidos->appends($request->query()); 
        
        // Manejo de AJAX si usas paginación o filtros dinámicos
        if ($request->ajax()) {
            return view('pedidos.table')->with('pedidos', $pedidos);
        }

        return view('pedidos.index')->with('pedidos', $pedidos);
    }

    /** CREAR */
    public function create()
    {
        // Select de clientes (mismo estilo que ventas)
        $clientes = DB::table('clientes')
            ->selectRaw("id_cliente, CONCAT(clie_nombre,' ', clie_apellido) AS cliente")
            ->pluck('cliente', 'id_cliente');

        // Sucursales (si existe la tabla)
        $sucursales = DB::table('sucursales')->pluck('descripcion', 'id_sucursal');

        // Usuario en sesión (solo para mostrar en la vista, el ID se guardará en store())
        $usuario = auth()->user()->name ?? '';

        // Estados para pedidos
        $estado = [
            'PENDIENTE' => 'PENDIENTE',
            'PROCESADO' => 'PROCESADO',
            'CANCELADO' => 'CANCELADO',
        ];

        return view('pedidos.create')->with(compact('clientes', 'sucursales', 'usuario', 'estado'));
    }

    /** GUARDAR */
    public function store(Request $request)
    {
        $input = $request->all();

        // Validaciones adaptadas al esquema de pedidos
        $validacion = Validator::make(
            $input,
            [
                'id_cliente'   => 'required|exists:clientes,id_cliente',
                'fecha_pedido' => 'required|date',
                'id_sucursal'  => 'nullable|exists:sucursales,id_sucursal',
                'estado'       => 'nullable|in:PENDIENTE,PROCESADO,CANCELADO',

                // Detalle (igual a ventas: codigo[], cantidad[], precio[])
                'codigo'       => 'required|array|min:1',
                'codigo.*'     => 'required|integer',
                'cantidad'     => 'required|array|min:1',
                'cantidad.*'   => 'required|numeric|min:1',
                'precio'       => 'required|array|min:1',
                'precio.*'     => 'required'
            ],
            [
                'codigo.required' => 'Debe agregar al menos un producto.',
            ]
        );

        if ($validacion->fails()) {
            return redirect()->back()->withErrors($validacion)->withInput();
        }

        // Usuario de la sesión que guardaremos en p.id_usuario
        $user_id = auth()->id();

        DB::beginTransaction();
        try {
            // Calcular total desde el detalle
            $total = 0;
            foreach ($input['codigo'] as $i => $codigo) {
                // normalizar "1.234" -> "1234"
                $monto = str_replace('.', '', $input['precio'][$i] ?? '0');
                $cantidad = (int)($input['cantidad'][$i] ?? 0);
                $total += ($cantidad * (float)$monto);
            }

            // Insertar cabecera de pedido
            $idPedido = DB::table('pedidos')->insertGetId([
                'id_cliente'   => $input['id_cliente'],
                'fecha_pedido' => $input['fecha_pedido'],
                'total_pedido' => $total,
                'id_sucursal'  => $input['id_sucursal'] ?? null,
                'estado'       => $input['estado'] ?? 'PENDIENTE',
                'id_usuario'   => $user_id, // NUEVO: guarda el usuario que registra
            ], 'id_pedido');

            // Insertar detalle del pedido (precio -> precio_unitario)
            foreach ($input['codigo'] as $i => $codigo) {
                $monto = str_replace('.', '', $input['precio'][$i] ?? '0');
                DB::table('detalle_pedido')->insert([
                    'id_pedido'       => $idPedido,
                    'id_producto'     => (int)$codigo,
                    'cantidad'        => (int)($input['cantidad'][$i] ?? 0),
                    'precio_unitario' => (float)$monto,
                ]);
            }

            DB::commit();
        } catch (\Exception $e) {
            Log::info('Error al registrar el pedido: ' . $e->getMessage());
            DB::rollback();
            return redirect()->back()->withErrors($e->getMessage());
        }

        Alert::success('Éxito', 'Pedido registrado exitosamente.');
        return redirect()->route('pedidos.index');
    }

    /** VER */
    public function show($id)
    {
        $pedido = DB::selectOne(
            "SELECT p.*, CONCAT(c.clie_nombre,' ', c.clie_apellido) AS cliente, c.clie_ci,
                    u.name AS usuario
               FROM pedidos p
               JOIN clientes c ON p.id_cliente = c.id_cliente
               JOIN users u   ON p.id_usuario = u.id
              WHERE p.id_pedido = ?",
            [$id]
        );

        if (empty($pedido)) {
            Alert::error('Error', 'Pedido no encontrado');
            return redirect()->route('pedidos.index');
        }

        // Detalle + productos
        $detalle = DB::select(
            "SELECT d.*, pr.descripcion
               FROM detalle_pedido d
               JOIN productos pr ON pr.id_producto = d.id_producto
              WHERE d.id_pedido = ?",
            [$id]
        );

        return view('pedidos.show')
            ->with('pedido', $pedido)
            ->with('detalle_pedido', $detalle);
    }

    /** EDITAR */
    public function edit($id)
    {
        $pedido = DB::selectOne('SELECT * FROM pedidos WHERE id_pedido = ?', [$id]);

        if (empty($pedido)) {
            Alert::error('Error', 'Pedido no encontrado');
            return redirect()->route('pedidos.index');
        }

        $clientes = DB::table('clientes')
            ->selectRaw("id_cliente, CONCAT(clie_nombre,' ', clie_apellido) AS cliente")
            ->pluck('cliente', 'id_cliente');

        $sucursales = DB::table('sucursales')->pluck('descripcion', 'id_sucursal');

        $usuario = auth()->user()->name ?? '';

        $detalle = DB::select(
            "SELECT d.*, pr.descripcion
               FROM detalle_pedido d
               JOIN productos pr ON pr.id_producto = d.id_producto
              WHERE d.id_pedido = ?",
            [$id]
        );

        return view('pedidos.edit', compact('pedido', 'clientes', 'sucursales', 'usuario'))
            ->with('detalle_pedido', $detalle);
    }

    /** ACTUALIZAR */
    public function update(Request $request, $id)
    {
        $input = $request->all();

        $pedido = DB::selectOne('SELECT * FROM pedidos WHERE id_pedido = ?', [$id]);

        if (empty($pedido)) {
            Alert::error('Error', 'Pedido no encontrado');
            return redirect()->route('pedidos.index');
        }

        $validacion = Validator::make(
            $input,
            [
                'fecha_pedido' => 'required|date',
                'id_sucursal'  => 'nullable|exists:sucursales,id_sucursal',
                'estado'       => 'nullable|in:PENDIENTE,PROCESADO,CANCELADO',

                'codigo'       => 'required|array|min:1',
                'codigo.*'     => 'required|integer',
                'cantidad'     => 'required|array|min:1',
                'cantidad.*'   => 'required|numeric|min:1',
                'precio_unitario'       => 'required|array|min:1',
                'precio_unitario.*'     => 'required'
            ]
        );

        if ($validacion->fails()) {
            return redirect()->back()->withErrors($validacion)->withInput();
        }

        DB::beginTransaction();
        try {
            // Recalcular total
            $total = 0;
            foreach ($input['codigo'] as $i => $codigo) {
                $monto = str_replace('.', '', $input['precio'][$i] ?? '0');
                $cantidad = (int)($input['cantidad'][$i] ?? 0);
                $total += ($cantidad * (float)$monto);
            }

            // Actualizar cabecera (no cambiamos id_usuario aquí)
            DB::update(
                'UPDATE pedidos SET fecha_pedido = ?, total_pedido = ?, id_sucursal = ?, estado = ? WHERE id_pedido = ?',
                [
                    $input['fecha_pedido'],
                    $total,
                    $input['id_sucursal'] ?? null,
                    $input['estado'] ?? 'PENDIENTE',
                    $id
                ]
            );

            // Upsert de detalle por id_producto
            foreach ($input['codigo'] as $i => $codigo) {
                $monto = str_replace('.', '', $input['precio'][$i] ?? '0');

                $detalle = DB::selectOne(
                    'SELECT * FROM detalle_pedido WHERE id_pedido = ? AND id_producto = ?',
                    [$id, (int)$codigo]
                );

                if (!empty($detalle)) {
                    DB::update(
                        'UPDATE detalle_pedido SET cantidad = ?, precio_unitario = ? WHERE id_pedido = ? AND id_producto = ?',
                        [
                            (int)($input['cantidad'][$i] ?? 0),
                            (float)$monto,
                            $id,
                            (int)$codigo
                        ]
                    );
                } else {
                    DB::table('detalle_pedido')->insert([
                        'id_pedido'       => $id,
                        'id_producto'     => (int)$codigo,
                        'cantidad'        => (int)($input['cantidad'][$i] ?? 0),
                        'precio_unitario' => (float)$monto,
                    ]);
                }
            }

            DB::commit();
        } catch (\Exception $e) {
            Log::info('Error al actualizar el pedido: ' . $e->getMessage());
            DB::rollback();
            return redirect()->back()->withErrors($e->getMessage());
        }

        Alert::success('Éxito', 'Pedido actualizado exitosamente.');
        return redirect()->route('pedidos.index');
    }

    /** ELIMINAR / ANULAR */
    public function destroy($id)
    {
        $pedido = DB::selectOne('SELECT * FROM pedidos WHERE id_pedido = ?', [$id]);

        if (empty($pedido)) {
            Alert::error('Error', 'Pedido no encontrado');
            return redirect()->route('pedidos.index');
        }

        DB::beginTransaction();
        try {
            // No borramos físico: marcamos estado CANCELADO
            DB::update('UPDATE pedidos SET estado = ? WHERE id_pedido = ?', ['CANCELADO', $id]);
            DB::commit();
        } catch (\Exception $e) {
            Log::info('Error al cancelar el pedido: ' . $e->getMessage());
            DB::rollback();
            return redirect()->back()->withErrors($e->getMessage());
        }

        Alert::success('Éxito', 'Pedido cancelado.');
        return redirect()->route('pedidos.index');
    }

    /** BUSCADOR DE PRODUCTO */
    public function buscarProducto(Request $request)
    {
        $cod_suc = $request->get('sucursal'); // id_sucursal actual del form
        $buscar  = $request->get('q');        // texto de búsqueda

        if (!empty($buscar)) {
            $buscar = mb_strtolower($buscar, 'UTF-8');
            $productos = DB::select(
                "SELECT p.*, s.cantidad, s.id_sucursal
                   FROM productos p
                   JOIN stocks s ON p.id_producto = s.id_producto
                  WHERE s.id_sucursal = ?
                    AND (LOWER(p.descripcion) LIKE ? OR CAST(p.id_producto AS TEXT) LIKE ?)
                  LIMIT 50",
                [$cod_suc, "%{$buscar}%", "%{$buscar}%"]
            );
        } else {
            // primeros 20 sin filtro
            $productos = DB::select(
                "SELECT p.*, s.cantidad, s.id_sucursal
                   FROM productos p
                   JOIN stocks s ON p.id_producto = s.id_producto
                  WHERE s.id_sucursal = ?
                  LIMIT 20",
                [$cod_suc]
            );
        }

        return view('pedidos.body_producto')->with('productos', $productos);
    }

    /** PDF */
    public function pdf()
    {
        $data = ['<h1>Pedido</h1>'];
        $pdf = Pdf::loadView('pedidos.invoice', compact('data'));
        return $pdf->download('pedido.pdf');
    }
}