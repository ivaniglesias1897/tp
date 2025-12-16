<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use RealRashid\SweetAlert\Facades\Alert;

class SucursalController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('permission:sucursales index')->only(['index']);
        $this->middleware('permission:sucursales create')->only(['create', 'store']);
        $this->middleware('permission:sucursales edit')->only(['edit', 'update']);
        $this->middleware('permission:sucursales destroy')->only(['destroy']);
    }

    public function index(Request $request)
    {
        $buscar = trim($request->get('buscar'));
        
        $sqlBase = 'SELECT s.*, c.descripcion as ciudad 
                    FROM sucursales s
                    JOIN ciudades c ON s.id_ciudad = c.id_ciudad';

        $sqlWhere = '';
        $bindings = [];
        $sqlOrder = 'ORDER BY s.id_sucursal DESC';

        if (!empty($buscar)) {
            if (ctype_digit($buscar) && strlen($buscar) <= 4) {
                $sqlWhere = "WHERE s.id_sucursal = ?";
                $bindings = [(int)$buscar];
            } else {
                $like = '%' . $buscar . '%';
                $sqlWhere = "WHERE (
                    s.descripcion ILIKE ? 
                    OR s.direccion ILIKE ? 
                    OR s.telefono ILIKE ? 
                    OR c.descripcion ILIKE ?
                    OR s.id_sucursal::text ILIKE ?
                    -- Búsqueda por Estado
                    OR (CASE WHEN s.estado = true THEN 'Activo' ELSE 'Inactivo' END) ILIKE ?
                )";
                $bindings = [$like, $like, $like, $like, $like, $like];
            }
        }
        
        $sucursalesData = DB::select($sqlBase . ' ' . $sqlWhere . ' ' . $sqlOrder, $bindings);
        
        $page = $request->input('page', 1);
        $perPage = 10;
        $total = count($sucursalesData);
        $items = array_slice($sucursalesData, ($page - 1) * $perPage, $perPage);

        $sucursales = new LengthAwarePaginator(
            $items, $total, $perPage, $page, 
            ['path' => $request->url(), 'query' => $request->query()]
        );
        
        if ($request->ajax()) {
            return view('sucursales.table')->with('sucursales', $sucursales);
        }
        
        return view('sucursales.index')->with('sucursales', $sucursales);
    }
    
    public function create()
    {
        // CAMBIO 1: Solo Ciudades ACTIVAS
        $ciudades = DB::table('ciudades')
                      ->where('estado', true)
                      ->pluck('descripcion', 'id_ciudad');

        return view('sucursales.create')->with('ciudades', $ciudades);
    }
    
    public function store(Request $request)
    {
        $input = $request->all();

        $validacion = Validator::make($input, [
            'descripcion' => 'required|unique:sucursales,descripcion', 
            'direccion' => 'required',
            'telefono' => 'required',
            'id_ciudad' => 'required|exists:ciudades,id_ciudad',
        ], [
            'descripcion.unique' => 'Ya existe una sucursal con esta descripción.',
            'id_ciudad.exists' => 'La ciudad seleccionada no existe.',
        ]);
        
        if ($validacion->fails()) {
            return redirect()->back()->withErrors($validacion)->withInput();
        }
        
        // CAMBIO 2: Insertar con estado TRUE
        DB::insert(
            'INSERT INTO sucursales (descripcion, direccion, telefono, id_ciudad, estado) VALUES (?, ?, ?, ?, ?)',
            [
                strtoupper($input['descripcion']),
                strtoupper($input['direccion']),
                $input['telefono'],
                $input['id_ciudad'],
                true // Estado Activo
            ]
        );

        Alert::toast('Sucursal creada con éxito', 'success');
        return redirect()->route('sucursales.index');  
    }
    
    public function edit($id)
    {
        $sucursales = DB::selectOne('SELECT * FROM sucursales WHERE id_sucursal = ?', [$id]);
        
        if (empty($sucursales)) {
            Alert::toast('Sucursal no encontrada', 'error');
            return redirect()->route('sucursales.index');
        }

        // CAMBIO 3: Bloqueo de seguridad
        if (!$sucursales->estado) {
            Alert::warning('Acción Denegada', 'No se puede editar una sucursal inactiva.');
            return redirect()->route('sucursales.index');
        }

        // CAMBIO 4: Ciudades Activas
        $ciudades = DB::table('ciudades')
                      ->where('estado', true)
                      ->pluck('descripcion', 'id_ciudad');

        return view('sucursales.edit')->with('sucursales', $sucursales)->with('ciudades', $ciudades);
    }
    
    public function update(Request $request, $id)
    {
        $sucursal = DB::selectOne('SELECT * FROM sucursales WHERE id_sucursal = ?', [$id]);
        
        if (empty($sucursal)) {
            Alert::toast('Sucursal no encontrada', 'error');
            return redirect()->route('sucursales.index');
        }
        
        // CAMBIO 5: Validación estado
        if (!$sucursal->estado) {
            Alert::warning('Error', 'No se puede actualizar una sucursal inactiva.');
            return redirect()->route('sucursales.index');
        }
        
        $input = $request->all();

        $validacion = Validator::make($input, [
            'descripcion' => 'required|unique:sucursales,descripcion,' . $id . ',id_sucursal', 
            'direccion' => 'required',
            'telefono' => 'required',
            'id_ciudad' => 'required|exists:ciudades,id_ciudad',
        ]);
        
        if ($validacion->fails()) {
            return redirect()->back()->withErrors($validacion)->withInput();
        }
        
        DB::update(
            'UPDATE sucursales SET descripcion = ?, direccion = ?, telefono = ?, id_ciudad = ? WHERE id_sucursal = ?',
            [
                strtoupper($input['descripcion']),
                strtoupper($input['direccion']),
                $input['telefono'],
                $input['id_ciudad'],
                $id
            ]
        );

        Alert::toast('Sucursal actualizada con éxito', 'success');
        return redirect()->route('sucursales.index');
    }
    
    /**
     * CAMBIO 6: Toggle de Estado
     */
    public function destroy($id)
    {
        // 1. Verificar existencia
        $sucursal = DB::selectOne('SELECT * FROM sucursales WHERE id_sucursal = ?', [$id]);
        
        if (empty($sucursal)) {
            Alert::toast('Sucursal no encontrada', 'error');
            return redirect()->route('sucursales.index');
        }
        
        // 2. Invertir Estado
        DB::update('UPDATE sucursales SET estado = NOT estado WHERE id_sucursal = ?', [$id]);
        
        // 3. Mensaje dinámico
        $accion = $sucursal->estado ? 'inactivada' : 'activada';
        
        Alert::toast("Sucursal $accion con éxito", 'success');
        return redirect()->route('sucursales.index');
    }
}