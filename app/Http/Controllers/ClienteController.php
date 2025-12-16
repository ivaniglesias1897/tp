<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use RealRashid\SweetAlert\Facades\Alert;
use Illuminate\Database\QueryException;

class ClienteController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('permission:clientes index')->only(['index']);
        $this->middleware('permission:clientes create')->only(['create', 'store']);
        $this->middleware('permission:clientes edit')->only(['edit', 'update']);
        $this->middleware('permission:clientes destroy')->only(['destroy']);
    }

    public function index(Request $request)
    {
        $buscar = $request->get('buscar');

        // 1. Consulta Base (Agregamos estado explícito para depuración si hiciera falta)
        $sqlBase = 'SELECT c.*, ciu.descripcion as ciudad, d.descripcion as departamento
                    FROM clientes c
                    JOIN ciudades ciu ON ciu.id_ciudad = c.id_ciudad
                    JOIN departamentos d ON d.id_departamento = c.id_departamento';

        $sqlWhere = '';
        $bindings = [];
        $sqlOrder = 'ORDER BY c.id_cliente DESC';

        if ($buscar) {
            $buscar_trim = trim($buscar);

            if (ctype_digit($buscar_trim) && strlen($buscar_trim) <= 4) {
                $sqlWhere = 'WHERE c.id_cliente = ?';
                $bindings = [(int) $buscar_trim];
                $sqlOrder = 'ORDER BY c.id_cliente ASC'; 
            } else {
                $like = '%' . $buscar_trim . '%';
                $sqlWhere = "WHERE (
                    c.clie_nombre ILIKE ? 
                    OR c.clie_apellido ILIKE ? 
                    OR c.clie_ci ILIKE ? 
                    OR c.clie_telefono ILIKE ? 
                    OR to_char(c.clie_fecha_nac, 'DD/MM/YYYY') ILIKE ?
                    OR ciu.descripcion ILIKE ?
                    OR d.descripcion ILIKE ?
                    OR c.id_cliente::text ILIKE ?
                    -- Búsqueda por estado (Activo/Inactivo)
                    OR (CASE WHEN c.estado = true THEN 'Activo' ELSE 'Inactivo' END) ILIKE ?
                )";
                
                $bindings = [$like, $like, $like, $like, $like, $like, $like, $like, $like];
            }
        } 

        $clientesData = DB::select($sqlBase . ' ' . $sqlWhere . ' ' . $sqlOrder, $bindings);

        $page = $request->input('page', 1);
        $perPage = 10;
        $total = count($clientesData);
        $items = array_slice($clientesData, ($page - 1) * $perPage, $perPage);

        $clientes = new LengthAwarePaginator(
            $items, $total, $perPage, $page,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        if ($request->ajax()) {
            return view('clientes.table')->with('clientes', $clientes);
        }

        return view('clientes.index')->with('clientes', $clientes);
    }

    public function create()
    {
        // CAMBIO 1: FILTRADO DE DESPLEGABLES
        // Solo mostramos ciudades y departamentos ACTIVOS (estado = true)
        // Defendible en examen: "No tiene sentido asignar un cliente a una ciudad clausurada"
        $ciudades = DB::table('ciudades')
                      ->where('estado', true)
                      ->pluck('descripcion', 'id_ciudad');

        $departamentos = DB::table('departamentos')
                           ->where('estado', true)
                           ->pluck('descripcion', 'id_departamento');

        return view('clientes.create')->with('ciudades', $ciudades)
            ->with('departamentos', $departamentos);
    }

    public function store(Request $request)
    {
        $input = $request->all();
        
        $validacion = Validator::make($input, [
            'clie_nombre' => 'required',
            'clie_apellido' => 'required',
            'clie_ci' => 'required|unique:clientes,clie_ci|max:8', 
            'clie_fecha_nac' => 'required|date|before_or_equal:today', 
            'id_departamento' => 'required|exists:departamentos,id_departamento',
            'id_ciudad' => 'required|exists:ciudades,id_ciudad',
        ], [
            'clie_ci.unique' => 'El número de C.I. ya está registrado.',
            'clie_fecha_nac.before_or_equal' => 'La fecha de nacimiento no es válida.',
        ]);

        if ($validacion->fails()) {
            return redirect()->back()->withErrors($validacion)->withInput(); 
        }

        $fecha_nac = Carbon::parse($input['clie_fecha_nac']);
        $edad = Carbon::now()->diffInYears($fecha_nac);

        if ($edad < 18) {
            Alert::error('Error', 'El cliente debe ser mayor de 18 años.');
            return redirect()->back()->withInput();
        }

        // CAMBIO 2: Insertamos estado = true explícitamente
        DB::insert('INSERT INTO clientes (
            clie_nombre, clie_apellido, clie_ci, clie_telefono, 
            clie_direccion, clie_fecha_nac, id_departamento, id_ciudad, estado
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)', [
            strtoupper($input['clie_nombre']),
            strtoupper($input['clie_apellido']),
            $input['clie_ci'],
            $input['clie_telefono'] ?? null,
            strtoupper($input['clie_direccion'] ?? null),
            $input['clie_fecha_nac'],
            $input['id_departamento'],
            $input['id_ciudad'],
            true // <--- Estado Activo por defecto
        ]);

        Alert::success('Éxito', 'Cliente creado correctamente.');
        return redirect(route('clientes.index'));
    }

    public function edit($id)
    {
        $clientes = DB::selectOne('SELECT * FROM clientes WHERE id_cliente = ?', [$id]);

        if (empty($clientes)) {
            Alert::error('Error', 'Cliente no encontrado.');
            return redirect(route('clientes.index'));
        }

        // CAMBIO 3: Bloqueo de Seguridad (No editar inactivos)
        if (!$clientes->estado) {
            Alert::warning('Acción Denegada', 'No se puede editar un cliente inactivo. Debe activarlo primero.');
            return redirect()->route('clientes.index');
        }

        // CAMBIO 4: Filtramos desplegables activos
        // (Opcional: Si el cliente pertenece a una ciudad vieja inactiva, podríamos traerla igual, 
        // pero por norma general, al editar exigimos moverlo a una ciudad activa).
        $ciudades = DB::table('ciudades')->where('estado', true)->pluck('descripcion', 'id_ciudad');
        $departamentos = DB::table('departamentos')->where('estado', true)->pluck('descripcion', 'id_departamento');

        return view('clientes.edit')->with('clientes', $clientes)
            ->with('ciudades', $ciudades)
            ->with('departamentos', $departamentos);
    }

    public function update(Request $request, $id)
    {
        $clientes = DB::selectOne('SELECT * FROM clientes WHERE id_cliente = ?', [$id]);

        if (empty($clientes)) {
            Alert::error('Error', 'Cliente no encontrado.');
            return redirect(route('clientes.index'));
        }

        // CAMBIO 5: Validación de estado antes de update
        if (!$clientes->estado) {
            Alert::warning('Error', 'No se puede actualizar un cliente inactivo.');
            return redirect()->route('clientes.index');
        }

        $input = $request->all();

        $validacion = Validator::make($input, [
            'clie_nombre' => 'required',
            'clie_apellido' => 'required',
            'clie_ci' => 'required|unique:clientes,clie_ci,' . $id . ',id_cliente|max:8', 
            'clie_fecha_nac' => 'required|date|before_or_equal:today',
            'id_departamento' => 'required|exists:departamentos,id_departamento',
            'id_ciudad' => 'required|exists:ciudades,id_ciudad',
        ]);

        if ($validacion->fails()) {
            return redirect()->back()->withErrors($validacion)->withInput();
        }

        $fecha_nac = Carbon::parse($input['clie_fecha_nac']);
        $edad = Carbon::now()->diffInYears($fecha_nac);

        if ($edad < 18) {
            Alert::error('Error', 'El cliente debe ser mayor de 18 años.');
            return redirect()->back()->withInput();
        }

        DB::update(
            'UPDATE clientes SET 
            clie_nombre = ?, clie_apellido = ?, clie_ci = ?, clie_telefono = ?, 
            clie_direccion = ?, clie_fecha_nac = ?, id_departamento = ?, id_ciudad = ? 
            WHERE id_cliente = ?',
            [
                strtoupper($input['clie_nombre']), 
                strtoupper($input['clie_apellido']), 
                $input['clie_ci'],
                $input['clie_telefono'] ?? null,
                strtoupper($input['clie_direccion'] ?? null),
                $input['clie_fecha_nac'],
                $input['id_departamento'],
                $input['id_ciudad'],
                $id
            ]
        );

        Alert::success('Éxito', 'Cliente actualizado correctamente.');
        return redirect(route('clientes.index'));
    }

    /**
     * CAMBIO 6: Destroy ahora es Toggle (Activar/Inactivar)
     */
    public function destroy($id)
    {
        // 1. Verificar existencia
        $cliente = DB::selectOne('SELECT * FROM clientes WHERE id_cliente = ?', [$id]);

        if (empty($cliente)) {
            Alert::error('Error', 'Cliente no encontrado.');
            return redirect(route('clientes.index'));
        }
        
        // 2. Invertir Estado (SQL Toggle)
        DB::update('UPDATE clientes SET estado = NOT estado WHERE id_cliente = ?', [$id]);
        
        // 3. Mensaje
        $accion = $cliente->estado ? 'inactivado' : 'activado';
        
        Alert::success('Éxito', "Cliente $accion correctamente.");
        return redirect(route('clientes.index'));
    }
}