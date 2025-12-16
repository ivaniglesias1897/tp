<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Pagination\LengthAwarePaginator;
use Carbon\Carbon;

class AuditoriaController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        // Protegemos el reporte general, pero dejamos libre el historial visual (timeline)
        $this->middleware('permission:auditoria index')->only(['index']);
    }

    /**
     * MÉTODO 1: Listado General (Reporte Global)
     * Este método alimenta la vista principal de auditoría (auditoria.index)
     */
    public function index(Request $request)
    {
        $buscar = $request->get('buscar');
        $sql = ''; 
        $bindings = [];

        if (!empty($buscar)) {
            // SEGURIDAD: Usamos '?' para evitar inyección SQL
            $sql = " AND (u.name iLIKE ? 
                             OR sc.descripcion iLIKE ? 
                             OR s.table_name iLIKE ? 
                             OR s.operation iLIKE ? 
                             OR CAST(s.id AS TEXT) iLIKE ?)";
            
            $term = '%' . $buscar . '%';
            $bindings = [$term, $term, $term, $term, $term];
        }

        // Consulta Principal con LEFT JOIN a sucursales
        $auditoria = DB::select(
            "SELECT 
                s.id AS id,
                COALESCE(sc.descripcion, 'N/A') AS sucursal,
                u.name AS usuario,
                s.operation AS operacion,
                s.table_name AS tabla,
                s.changed_at AS fecha,
                old_data AS anterior,
                new_data AS nuevo
             FROM audit.log s
             JOIN users u ON s.user_id = u.id
             LEFT JOIN sucursales sc ON u.id_sucursal = sc.id_sucursal
             WHERE 1=1 " . $sql . "
             ORDER BY s.id DESC",
             $bindings
        );

        // Paginación Manual
        $page = $request->input('page', 1);
        $perPage = 10;
        $total = count($auditoria);
        $items = array_slice($auditoria, ($page - 1) * $perPage, $perPage);

        $auditoria = new LengthAwarePaginator(
            $items, $total, $perPage, $page,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        if ($request->ajax()) {
            return view('auditoria.table')->with('auditoria', $auditoria);
        }

        return view('auditoria.index')->with('auditoria', $auditoria);
    }

    /**
     * MÉTODO 2: Historial Visual (GLOBAL)
     * Se usa para los modales en Productos, Clientes, Ventas, etc.
     */
    public function getHistorial($tabla, $id)
    {
        // 1. CONFIGURACIÓN: Mapeo de Tablas a sus Claves Primarias
        // Aquí agregas cualquier tabla nueva que quieras auditar visualmente
        $pkMap = [
            'productos'     => 'id_producto',
            'clientes'      => 'id_cliente', 
            'proveedores'   => 'id_proveedor', 
            'users'         => 'id',
            'ventas'        => 'id_venta',
            'cargos'        => 'id_cargo',
            'sucursales'    => 'id_sucursal',
            'marcas'        => 'id_marca',
            'departamentos' => 'id_departamento',
            'ciudades'      => 'id_ciudad',
            'sucursales'    => 'id_sucursal',
            'cajas'         => 'id_caja',
            'productos'     => 'id_producto',
        ];

        // Validación de seguridad
        if (!isset($pkMap[$tabla])) {
            return response()->json(['error' => 'Tabla no configurada para historial'], 400);
        }

        $pkField = $pkMap[$tabla];

        // 2. CONSULTA: Buscar en el JSON de audit.log
        // Usamos (string)$id para asegurar que coincida con el texto en el JSON
        $logs = DB::select("
            SELECT a.*, u.name as usuario_nombre
            FROM audit.log a
            LEFT JOIN users u ON a.user_id = u.id
            WHERE table_name = ?
            AND (
                (old_data->>? = ?) OR 
                (new_data->>? = ?)
            )
            ORDER BY changed_at DESC
        ", [$tabla, $pkField, (string)$id, $pkField, (string)$id]);

        // 3. PROCESAMIENTO: Convertir datos crudos a historial legible
        $historial = [];

        foreach ($logs as $log) {
            $detalles = [];
            $old = json_decode($log->old_data, true);
            $new = json_decode($log->new_data, true);

            // Limpieza de la operación
            $operacion = strtoupper(trim($log->operation));
            $accionNormalizada = 'UPDATE'; 

            // --- A) INSERT ---
            if ($operacion == 'INSERT' || $operacion == 'AGREGA') {
                $detalles[] = "Registro creado en el sistema.";
                $accionNormalizada = 'INSERT';
            } 
            // --- B) DELETE ---
            elseif ($operacion == 'DELETE' || $operacion == 'ELIMINA') {
                $detalles[] = "Registro eliminado del sistema.";
                $accionNormalizada = 'DELETE';
            } 
            // --- C) UPDATE ---
            elseif ($operacion == 'UPDATE' || $operacion == 'MODIFICA') {
                $accionNormalizada = 'UPDATE';
                if (is_array($new)) {
                    foreach ($new as $key => $value) {
                        // Ignorar campos de control interno
                        if (in_array($key, ['updated_at', 'created_at', 'deleted_at'])) continue;

                        $valAnt = $old[$key] ?? '';
                        
                        // Si el valor cambió, lo registramos
                        if ($valAnt != $value) {
                            $campoNombre = ucfirst(str_replace('_', ' ', $key));
                            
                            // --- MEJORA PROACTIVA: Formateo de Booleanos (Estado) ---
                            // Si el campo es 'estado', traducimos true/false a texto legible
                            if ($key === 'estado') {
                                $showAnt = $valAnt ? 'Activo' : 'Inactivo';
                                $showNew = $value  ? 'Activo' : 'Inactivo';
                            } else {
                                // Lógica estándar para otros campos
                                $showAnt = ($valAnt === '' || $valAnt === null) ? '<em>(Vacío)</em>' : $valAnt;
                                $showNew = ($value === '' || $value === null) ? '<em>(Vacío)</em>' : $value;
                            }
                            // ---------------------------------------------------------

                            $detalles[] = "<b>$campoNombre</b> cambió de <span class='text-danger'>$showAnt</span> a <span class='text-success'>$showNew</span>";
                        }
                    }
                }
            }

            // Solo agregamos si hay algo que mostrar
            if (count($detalles) > 0 || $accionNormalizada != 'UPDATE') {
                $historial[] = [
                    'fecha'     => Carbon::parse($log->changed_at)->format('d/m/Y H:i'),
                    'usuario' => $log->usuario_nombre ?? 'Sistema',
                    'accion'    => $accionNormalizada,
                    'cambios' => $detalles
                ];
            }
        }

        // 4. RESPUESTA: Devolvemos la vista parcial que se carga en el modal
        return view('layouts.auditoria_timeline', compact('historial'));
    }
}