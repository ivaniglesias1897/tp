<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use RealRashid\SweetAlert\Facades\Alert;

class CajaController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('permission:cajas index')->only(['index']);
        $this->middleware('permission:cajas create')->only(['create', 'store']);
        $this->middleware('permission:cajas edit')->only(['edit', 'update']);
        $this->middleware('permission:cajas destroy')->only(['destroy']);
    }

    public function index(Request $request)
    {
        $buscar = trim($request->get('buscar'));
        
        $sqlBase = 'SELECT c.*, s.descripcion as sucursal 
                    FROM cajas c
                    JOIN sucursales s ON c.id_sucursal = s.id_sucursal';
        
        $sqlWhere = '';
        $bindings = [];
        $sqlOrder = 'ORDER BY c.id_caja ASC';

        if (!empty($buscar)) {
            if (ctype_digit($buscar) && strlen($buscar) <= 4) {
                $sqlWhere = "WHERE c.id_caja = ?";
                $bindings = [(int)$buscar];
            } else {
                $like = '%' . $buscar . '%';
                $sqlWhere = "WHERE (
                    c.descripcion ILIKE ? 
                    OR s.descripcion ILIKE ? 
                    OR c.punto_expedicion ILIKE ? 
                    OR c.ultima_factura_impresa::text ILIKE ? 
                    OR c.id_caja::text ILIKE ?
                    -- Búsqueda por estado
                    OR (CASE WHEN c.estado = true THEN 'Activo' ELSE 'Inactivo' END) ILIKE ?
                )";
                $bindings = [$like, $like, $like, $like, $like, $like];
            }
        }
        
        $cajasData = DB::select($sqlBase . ' ' . $sqlWhere . ' ' . $sqlOrder, $bindings);
        
        $page = $request->input('page', 1);
        $perPage = 10;
        $total = count($cajasData);
        $items = array_slice($cajasData, ($page - 1) * $perPage, $perPage);

        $cajas = new LengthAwarePaginator(
            $items, $total, $perPage, $page, 
            ['path' => $request->url(), 'query' => $request->query()]
        );
        
        if ($request->ajax()) {
            return view('cajas.table')->with('cajas', $cajas);
        }

        return view('cajas.index')->with('cajas', $cajas);
    }
    
    public function create()
    {
        // CAMBIO 1: Solo Sucursales ACTIVAS
        // Defensa: "No tiene sentido asignar una caja a una sucursal cerrada."
        $sucursales = DB::table('sucursales')
                        ->where('estado', true)
                        ->pluck('descripcion', 'id_sucursal');

        return view('cajas.create')->with('sucursales', $sucursales);
    }
    
    public function store(Request $request)
    {
        $input = $request->all();

        $validacion = Validator::make($input, [
            'descripcion' => 'required|unique:cajas,descripcion',
            'punto_expedicion' => 'required|unique:cajas,punto_expedicion',
            'id_sucursal' => 'required|exists:sucursales,id_sucursal',
            'ultima_factura_impresa' => 'required|integer|min:0', 
        ], [
            'descripcion.unique' => 'Ya existe una caja con esta descripción.',
            'punto_expedicion.unique' => 'Este Punto de Expedición ya está asignado.',
        ]);
        
        if ($validacion->fails()) {
            return redirect()->back()->withErrors($validacion)->withInput();
        }
        
        // CAMBIO 2: Insertar con estado TRUE
        DB::insert(
            'INSERT INTO cajas (descripcion, id_sucursal, punto_expedicion, ultima_factura_impresa, estado) VALUES (?, ?, ?, ?, ?)',
            [
                strtoupper($input['descripcion']),
                $input['id_sucursal'],
                $input['punto_expedicion'], 
                $input['ultima_factura_impresa'],
                true // Estado Activo
            ]
        );

        Alert::toast('Caja creada con éxito', 'success');
        return redirect()->route('cajas.index');
    }
    
    public function edit($id)
    {
        $caja = DB::selectOne('SELECT * FROM cajas WHERE id_caja = ?', [$id]);
        
        if (empty($caja)) {
            Alert::toast('Caja no encontrada', 'error');
            return redirect()->route('cajas.index');
        }

        // CAMBIO 3: Bloqueo de seguridad
        if (!$caja->estado) {
            Alert::warning('Acción Denegada', 'No se puede editar una caja inactiva.');
            return redirect()->route('cajas.index');
        }
        
        // CAMBIO 4: Sucursales activas
        $sucursales = DB::table('sucursales')
                        ->where('estado', true)
                        ->pluck('descripcion', 'id_sucursal');
        
        // Corrección de variable: paso 'cajas' (plural) porque así lo espera tu vista edit, 
        // aunque el objeto es singular.
        return view('cajas.edit')->with('cajas', $caja)->with('sucursales', $sucursales);
    }
    
    public function update(Request $request, $id)
    {
        $caja = DB::selectOne('SELECT * FROM cajas WHERE id_caja = ?', [$id]);
        
        if (empty($caja)) {
            Alert::toast('Caja no encontrada', 'error');
            return redirect()->route('cajas.index');
        }

        // CAMBIO 5: Validación estado
        if (!$caja->estado) {
            Alert::warning('Error', 'No se puede actualizar una caja inactiva.');
            return redirect()->route('cajas.index');
        }
        
        $input = $request->all();
        
        $validacion = Validator::make($input, [
            'descripcion' => 'required|unique:cajas,descripcion,' . $id . ',id_caja',
            'punto_expedicion' => 'required|unique:cajas,punto_expedicion,' . $id . ',id_caja',
            'id_sucursal' => 'required|exists:sucursales,id_sucursal',
            'ultima_factura_impresa' => 'required|integer|min:0',
        ]);
        
        if ($validacion->fails()) {
            return redirect()->back()->withErrors($validacion)->withInput();
        }
        
        DB::update(
            'UPDATE cajas SET descripcion = ?, id_sucursal = ?, punto_expedicion = ?, ultima_factura_impresa = ? WHERE id_caja = ?',
            [
                strtoupper($input['descripcion']),
                $input['id_sucursal'],
                $input['punto_expedicion'],
                $input['ultima_factura_impresa'],
                $id
            ]
        );

        Alert::toast('Caja actualizada con éxito', 'success');
        return redirect()->route('cajas.index');
    }
    
    /**
     * CAMBIO 6: Toggle de Estado
     */
    public function destroy($id)
    {
        // 1. Verificar existencia
        $caja = DB::selectOne('SELECT * FROM cajas WHERE id_caja = ?', [$id]);
        
        if (empty($caja)) {
            Alert::toast('Caja no encontrada', 'error');
            return redirect()->route('cajas.index');
        }
        
        // 2. Invertir Estado
        DB::update('UPDATE cajas SET estado = NOT estado WHERE id_caja = ?', [$id]);
        
        // 3. Mensaje dinámico
        $accion = $caja->estado ? 'inactivada' : 'activada';
        
        Alert::toast("Caja $accion con éxito", 'success');
        return redirect()->route('cajas.index');
    }
}