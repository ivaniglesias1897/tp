<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Pagination\LengthAwarePaginator;
use Carbon\Carbon;
use RealRashid\SweetAlert\Facades\Alert; 
use Illuminate\Support\Facades\Log; 

class CuentasACobrarController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('permission:cuentasacobrar index')->only(['index']);
    }

    /**
     * Muestra la lista de cuentas a cobrar
     */
    public function index(Request $request)
    {
        $buscar = trim($request->get('buscar'));
        
        // 1. Consulta Base con JOINS necesarios
        // CORRECCIÓN: Agregamos 'ca.nro_cuota as nro_cuotas' para que coincida con tu vista table.blade.php
        $sqlBase = "SELECT ca.*, 
                           ca.nro_cuota as nro_cuotas,
                           CONCAT(c.clie_nombre, ' ', c.clie_apellido) as cliente, 
                           v.factura_nro, 
                           v.fecha_venta
                    FROM cuentas_a_cobrar ca
                    JOIN clientes c ON ca.id_cliente = c.id_cliente
                    JOIN ventas v ON ca.id_venta = v.id_venta";

        $sqlWhere = '';
        $bindings = [];
        
        // Orden por defecto: Vencimientos más próximos primero, luego por ID
        $sqlOrder = ' ORDER BY ca.vencimiento ASC, ca.id_cta ASC';

        if (!empty($buscar)) {
            
            // CASO A: Búsqueda por ID exacto (si es numérico y corto)
            if (ctype_digit($buscar) && strlen($buscar) <= 5) {
                
                $sqlWhere = " WHERE ca.id_cta = ?";
                $bindings = [(int)$buscar];
                
            } else {
                
                // CASO B: Búsqueda General en los 8 campos solicitados
                $like = '%' . $buscar . '%';
                
                $sqlWhere = " WHERE (
                    -- 1. Nro Cuenta
                    CAST(ca.id_cta AS TEXT) ILIKE ?
                    
                    -- 2. Cliente (Nombre Completo)
                    OR CONCAT(c.clie_nombre, ' ', c.clie_apellido) ILIKE ?
                    
                    -- 3. N° de Factura
                    OR v.factura_nro ILIKE ?
                    
                    -- 4. Fecha de Venta (Formateada a DD/MM/YYYY)
                    OR to_char(v.fecha_venta, 'DD/MM/YYYY') ILIKE ?
                    
                    -- 5. Monto Original (Importe)
                    OR CAST(ca.importe AS TEXT) ILIKE ?
                    
                    -- 6. Saldo Pendiente
                    OR CAST(ca.saldo AS TEXT) ILIKE ?
                    
                    -- 7. Estado
                    OR ca.estado ILIKE ?
                    
                    -- 8. Vencimiento (Formateada a DD/MM/YYYY)
                    OR to_char(ca.vencimiento, 'DD/MM/YYYY') ILIKE ?
                )";

                // Bindings en orden (8 parámetros)
                $bindings = [
                    $like, // id_cta
                    $like, // cliente
                    $like, // factura_nro
                    $like, // fecha_venta
                    $like, // importe
                    $like, // saldo
                    $like, // estado
                    $like  // vencimiento
                ];
            }
        }

        // 2. Ejecutar la consulta
        $cuentasacobrar = DB::select($sqlBase . $sqlWhere . $sqlOrder, $bindings);

        // 3. Paginación Manual
        $page = $request->input('page', 1);
        $perPage = 10;
        $total = count($cuentasacobrar);
        $items = array_slice($cuentasacobrar, ($page - 1) * $perPage, $perPage);

        $cuentasacobrar = new LengthAwarePaginator(
            $items, $total, $perPage, $page,
            ['path' => $request->url(), 'query' => $request->query()]
        );
        
        // Adjuntamos parámetros para mantener la búsqueda
        $cuentasacobrar->appends($request->query());

        // 4. Retorno de vista (AJAX o Normal)
        if ($request->ajax()) {
            return view('cuentasacobrar.table')->with('cuentasacobrar', $cuentasacobrar);
        }

        return view('cuentasacobrar.index')->with('cuentasacobrar', $cuentasacobrar);
    }

    /**
     * Paso 1: Mostrar el formulario de cobro y el historial
     */
    public function cobrar($id)
    {
        // 1. Buscar la cuenta
        $cuenta = DB::selectOne("
            SELECT ca.*, CONCAT(c.clie_nombre, ' ', c.clie_apellido) as cliente, v.factura_nro, v.cantidad_cuota
            FROM cuentas_a_cobrar ca
            JOIN clientes c ON ca.id_cliente = c.id_cliente
            JOIN ventas v ON ca.id_venta = v.id_venta
            WHERE ca.id_cta = ?
        ", [$id]);

        if (!$cuenta) {
            Alert::toast('La cuenta no existe', 'error');
            return redirect()->route('cuentasacobrar.index');
        }

        // 2. Métodos de pago activos
        $metodos_pago = DB::table('metodo_pagos')->where('estado', true)->pluck('descripcion', 'id_metodo_pago');

        // 3. (NUEVO) Historial de cobros de esta cuenta
        // Vital para poder listar los cobros en la vista y dar opción de anular
        $cobrosRealizados = DB::select("
            SELECT c.*, mp.descripcion as metodo, u.name as usuario
            FROM cobros c
            JOIN metodo_pagos mp ON c.id_metodo_pago = mp.id_metodo_pago
            LEFT JOIN users u ON c.user_id = u.id
            WHERE c.id_cta = ?
            ORDER BY c.id_cobro DESC
        ", [$id]);

        return view('cuentasacobrar.cobrar', compact('cuenta', 'metodos_pago', 'cobrosRealizados'));
    }

    /**
     * Paso 2: Procesar el cobro
     */
    public function guardarCobro(Request $request, $id)
    {
        $input = $request->all();

        $cuenta = DB::selectOne("SELECT * FROM cuentas_a_cobrar WHERE id_cta = ?", [$id]);

        if (!$cuenta) {
            Alert::toast('Cuenta no encontrada', 'error');
            return redirect()->back();
        }

        $saldoPendiente = $cuenta->saldo ?? $cuenta->importe;

        DB::beginTransaction();
        try {
            $total_pagado = 0;

            if ($request->has('forma_pago')) {
                foreach ($input['forma_pago'] as $key => $metodo) {
                    $importe = str_replace('.', '', $input['importe'][$key]);

                    if (!is_numeric($importe) || $importe <= 0) {
                        throw new \Exception("El importe debe ser un número mayor a 0");
                    }

                    $total_pagado += $importe;

                    DB::insert(
                        'INSERT INTO cobros(id_venta, id_cta, user_id, id_metodo_pago, cobro_fecha, cobro_importe, cobro_estado, nro_voucher, updated_at) 
                        VALUES(?, ?, ?, ?, ?, ?, ?, ?, NOW())',
                        [
                            $cuenta->id_venta,
                            $cuenta->id_cta, 
                            auth()->user()->id,
                            $metodo,
                            Carbon::now()->format('Y-m-d'),
                            $importe,
                            'COBRADO',
                            $input['nro_voucher'][$key] ?? null
                        ]
                    );
                }

                if ($total_pagado > $saldoPendiente) {
                    throw new \Exception("El monto ingresado supera el saldo pendiente.");
                }

                $nuevoSaldo = $saldoPendiente - $total_pagado;
                $nuevoEstado = ($nuevoSaldo <= 0) ? 'PAGADO' : 'PENDIENTE';

                DB::update("
                    UPDATE cuentas_a_cobrar 
                    SET saldo = ?, estado = ?, updated_at = NOW() 
                    WHERE id_cta = ?
                ", [$nuevoSaldo, $nuevoEstado, $id]);

                DB::commit();
                Alert::toast('Cobro registrado correctamente.', 'success');
                return redirect()->route('cuentasacobrar.index');

            } else {
                throw new \Exception("Debe agregar al menos una forma de pago.");
            }

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Error al cobrar cuenta: " . $e->getMessage());
            Alert::toast($e->getMessage(), 'error');
            return redirect()->back()->withInput();
        }
    }

    /**
     * (NUEVO) Anula un cobro y restaura el saldo de la cuenta del cliente.
     */
    public function anularCobro($id_cobro)
    {
        DB::beginTransaction();
        try {
            // 1. Buscar el cobro
            $cobro = DB::selectOne("SELECT * FROM cobros WHERE id_cobro = ?", [$id_cobro]);

            if (!$cobro) throw new \Exception("El cobro no existe.");
            if ($cobro->cobro_estado == 'ANULADO') throw new \Exception("Este cobro ya fue anulado.");

            // 2. Buscar la cuenta asociada
            $cuenta = DB::selectOne("SELECT * FROM cuentas_a_cobrar WHERE id_cta = ?", [$cobro->id_cta]);

            // 3. Restaurar saldo (SUMAMOS lo que habíamos restado)
            $saldoActual = $cuenta->saldo ?? 0;
            $nuevoSaldo = $saldoActual + $cobro->cobro_importe;

            // 4. Actualizar cuenta (Siempre vuelve a PENDIENTE porque ahora debe más dinero)
            DB::update("
                UPDATE cuentas_a_cobrar 
                SET saldo = ?, estado = 'PENDIENTE', updated_at = NOW() 
                WHERE id_cta = ?
            ", [$nuevoSaldo, $cobro->id_cta]);

            // 5. Marcar cobro como ANULADO
            DB::update("UPDATE cobros SET cobro_estado = 'ANULADO', updated_at = NOW() WHERE id_cobro = ?", [$id_cobro]);

            DB::commit();
            Alert::toast('Cobro anulado. El saldo de la cuenta ha aumentado.', 'success');
            return redirect()->back();

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Error anulando cobro: " . $e->getMessage());
            Alert::toast("Error: " . $e->getMessage(), 'error');
            return redirect()->back();
        }
    }
}