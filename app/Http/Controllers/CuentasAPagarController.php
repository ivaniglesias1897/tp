<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Pagination\LengthAwarePaginator;
use RealRashid\SweetAlert\Facades\Alert;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class CuentasAPagarController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('permission:cuentasapagar index')->only(['index']);
    }

    /**
     * Muestra la lista de cuentas a pagar con buscador y paginación.
     */
    public function index(Request $request)
    {
        $buscar = trim($request->get('buscar'));
        
        // 1. Consulta Base con JOINS necesarios
        $sqlBase = "SELECT cap.*, 
                           cap.nro_cuenta as nro_cuotas,
                           p.descripcion as proveedor, 
                           c.factura, 
                           c.fecha_compra
                    FROM cuentas_a_pagar cap
                    JOIN proveedores p ON cap.id_proveedor = p.id_proveedor
                    JOIN compras c ON cap.id_compra = c.id_compra";

        $sqlWhere = '';
        $bindings = [];
        
        // Orden por defecto: Vencimientos más próximos primero, luego por ID
        $sqlOrder = ' ORDER BY cap.vencimiento ASC, cap.id_cta ASC';

        if (!empty($buscar)) {
            
            // CASO A: Búsqueda por ID exacto (si es numérico y corto)
            if (ctype_digit($buscar) && strlen($buscar) <= 5) {
                
                $sqlWhere = " WHERE cap.id_cta = ?";
                $bindings = [(int)$buscar];
                
            } else {
                
                // CASO B: Búsqueda General en los 8 campos solicitados
                $like = '%' . $buscar . '%';
                
                $sqlWhere = " WHERE (
                    -- 1. Nro Cuenta (ID)
                    CAST(cap.id_cta AS TEXT) ILIKE ?
                    
                    -- 2. Proveedor
                    OR p.descripcion ILIKE ?
                    
                    -- 3. N° Factura
                    OR c.factura ILIKE ?
                    
                    -- 4. Fecha Compra (Formateada a DD/MM/YYYY)
                    OR to_char(c.fecha_compra, 'DD/MM/YYYY') ILIKE ?
                    
                    -- 5. Monto Original (Importe)
                    OR CAST(cap.importe AS TEXT) ILIKE ?
                    
                    -- 6. Saldo Pendiente
                    OR CAST(cap.saldo AS TEXT) ILIKE ?
                    
                    -- 7. Estado
                    OR cap.estado ILIKE ?
                    
                    -- 8. Vencimiento (Formateada a DD/MM/YYYY)
                    OR to_char(cap.vencimiento, 'DD/MM/YYYY') ILIKE ?
                )";

                // Bindings en orden (8 parámetros)
                $bindings = [
                    $like, // id_cta
                    $like, // proveedor
                    $like, // factura
                    $like, // fecha_compra
                    $like, // importe
                    $like, // saldo
                    $like, // estado
                    $like  // vencimiento
                ];
            }
        }

        // 2. Ejecutar la consulta
        $cuentasapagar = DB::select($sqlBase . $sqlWhere . $sqlOrder, $bindings);

        // 3. Paginación Manual
        $page = $request->input('page', 1);
        $perPage = 10;
        $total = count($cuentasapagar);
        $items = array_slice($cuentasapagar, ($page - 1) * $perPage, $perPage);

        $cuentasapagar = new LengthAwarePaginator(
            $items, $total, $perPage, $page,
            ['path' => $request->url(), 'query' => $request->query()]
        );
        
        // Adjuntamos parámetros para mantener la búsqueda
        $cuentasapagar->appends($request->query());

        // 4. Retorno de vista (AJAX o Normal)
        if ($request->ajax()) {
            return view('cuentasapagar.table')->with('cuentasapagar', $cuentasapagar);
        }

        return view('cuentasapagar.index')->with('cuentasapagar', $cuentasapagar);
    }
    /**
     * Muestra el formulario para registrar un pago y el historial de pagos previos.
     */
    public function pagar($id)
    {
        // 1. Buscar la cuenta
        $cuenta = DB::selectOne("
            SELECT cap.*, 
                   p.descripcion as proveedor, 
                   c.factura, 
                   c.cantidad_cuotas
            FROM cuentas_a_pagar cap
            JOIN proveedores p ON cap.id_proveedor = p.id_proveedor
            JOIN compras c ON cap.id_compra = c.id_compra
            WHERE cap.id_cta = ?
        ", [$id]);

        if (!$cuenta) {
            Alert::toast('La cuenta por pagar no existe', 'error');
            return redirect()->route('cuentasapagar.index');
        }

        // 2. Obtener métodos de pago activos
        $metodos_pago = DB::table('metodo_pagos')
            ->where('estado', true)
            ->pluck('descripcion', 'id_metodo_pago');

        // 3. Buscar el historial de pagos para poder anularlos si es necesario
        $pagosRealizados = DB::select("
            SELECT pp.*, mp.descripcion as metodo, u.name as usuario
            FROM pagos_proveedores pp
            JOIN metodo_pagos mp ON pp.id_metodo_pago = mp.id_metodo_pago
            LEFT JOIN users u ON pp.user_id = u.id
            WHERE pp.id_cta = ?
            ORDER BY pp.id_pago DESC
        ", [$id]);

        return view('cuentasapagar.pagar', compact('cuenta', 'metodos_pago', 'pagosRealizados'));
    }

    /**
     * Procesa el pago, actualiza saldo y registra en pagos_proveedores.
     */
    public function guardarPago(Request $request, $id)
    {
        $input = $request->all();

        $cuenta = DB::selectOne("SELECT * FROM cuentas_a_pagar WHERE id_cta = ?", [$id]);

        if (!$cuenta) {
            Alert::toast('Cuenta no encontrada', 'error');
            return redirect()->back();
        }

        $saldoPendiente = $cuenta->saldo ?? $cuenta->importe;

        DB::beginTransaction();
        try {
            $total_pagado_ahora = 0;

            if ($request->has('forma_pago')) {
                foreach ($input['forma_pago'] as $key => $metodo) {
                    // Limpiar importe
                    $monto = str_replace('.', '', $input['importe'][$key]);

                    if (!is_numeric($monto) || $monto <= 0) {
                        throw new \Exception("El importe debe ser mayor a 0");
                    }

                    $total_pagado_ahora += $monto;

                    // Insertar pago con estado ACTIVO
                    DB::insert(
                        'INSERT INTO pagos_proveedores(
                            id_compra, id_cta, id_metodo_pago, fecha_pago, monto_pago, 
                            nro_recibo, observacion, user_id, created_at, estado
                        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW(), ?)',
                        [
                            $cuenta->id_compra, $cuenta->id_cta, $metodo, Carbon::now()->format('Y-m-d'),
                            $monto, $input['nro_recibo'][$key] ?? null, $input['observacion'][$key] ?? null,
                            auth()->user()->id, 'ACTIVO'
                        ]
                    );
                }

                if ($total_pagado_ahora > $saldoPendiente) {
                    throw new \Exception("El monto a pagar supera el saldo de la deuda");
                }

                $nuevoSaldo = $saldoPendiente - $total_pagado_ahora;
                $nuevoEstado = ($nuevoSaldo <= 0) ? 'PAGADO' : 'PENDIENTE';

                // Actualizar cuenta
                DB::update("
                    UPDATE cuentas_a_pagar 
                    SET saldo = ?, estado = ?, updated_at = NOW() 
                    WHERE id_cta = ?
                ", [$nuevoSaldo, $nuevoEstado, $id]);

                DB::commit();
                Alert::toast('Pago registrado exitosamente.', 'success');
                return redirect()->route('cuentasapagar.index');
            } else {
                throw new \Exception("Debe ingresar al menos un detalle de pago.");
            }

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Error al pagar proveedor: " . $e->getMessage());
            Alert::toast($e->getMessage(), 'error');
            return redirect()->back()->withInput();
        }
    }

    /**
     * Anula un pago realizado y restaura el saldo de la deuda.
     */
    public function anularPago($id_pago)
    {
        DB::beginTransaction();
        try {
            // 1. Buscar el pago
            $pago = DB::selectOne("SELECT * FROM pagos_proveedores WHERE id_pago = ?", [$id_pago]);

            if (!$pago) throw new \Exception("El pago no existe.");
            if ($pago->estado == 'ANULADO') throw new \Exception("Este pago ya fue anulado.");

            // 2. Buscar la cuenta
            $cuenta = DB::selectOne("SELECT * FROM cuentas_a_pagar WHERE id_cta = ?", [$pago->id_cta]);

            // 3. Restaurar saldo (sumar lo que se había pagado)
            $saldoActual = $cuenta->saldo ?? 0;
            $nuevoSaldo = $saldoActual + $pago->monto_pago;

            // 4. Actualizar cuenta (siempre vuelve a PENDIENTE porque ahora debe plata de nuevo)
            DB::update("
                UPDATE cuentas_a_pagar 
                SET saldo = ?, estado = 'PENDIENTE', updated_at = NOW() 
                WHERE id_cta = ?
            ", [$nuevoSaldo, $pago->id_cta]);

            // 5. Marcar pago como anulado
            DB::update("UPDATE pagos_proveedores SET estado = 'ANULADO', updated_at = NOW() WHERE id_pago = ?", [$id_pago]);

            DB::commit();
            Alert::toast('Pago anulado. Saldo restaurado.', 'success');
            return redirect()->back();

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Error anulando pago: " . $e->getMessage());
            Alert::toast("Error: " . $e->getMessage(), 'error');
            return redirect()->back();
        }
    }
}