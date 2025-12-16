<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use RealRashid\SweetAlert\Facades\Alert;

class ProveedorController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('permission:proveedores index')->only(['index']);
        $this->middleware('permission:proveedores create')->only(['create', 'store']);
        $this->middleware('permission:proveedores edit')->only(['edit', 'update']);
        $this->middleware('permission:proveedores destroy')->only(['destroy']);
    }

    public function index(Request $request)
    {
        $buscar = trim($request->get('buscar'));
        
        $sqlBase = 'SELECT * FROM proveedores';
        $sqlWhere = '';
        $bindings = [];
        $sqlOrder = 'ORDER BY id_proveedor DESC';

        if (!empty($buscar)) {
            if (ctype_digit($buscar) && strlen($buscar) <= 4) {
                $sqlWhere = "WHERE id_proveedor = ?";
                $bindings = [(int)$buscar];
                $sqlOrder = 'ORDER BY id_proveedor ASC';
            } else {
                $like = '%' . $buscar . '%';
                $sqlWhere = "WHERE (
                    descripcion ILIKE ? 
                    OR direccion ILIKE ? 
                    OR telefono ILIKE ?
                    OR id_proveedor::text ILIKE ?
                    -- Búsqueda por Estado
                    OR (CASE WHEN estado = true THEN 'Activo' ELSE 'Inactivo' END) ILIKE ?
                )";
                $bindings = [$like, $like, $like, $like, $like];
            }
        }
        
        $proveedoresData = DB::select($sqlBase . ' ' . $sqlWhere . ' ' . $sqlOrder, $bindings);
        
        $page = $request->input('page', 1);
        $perPage = 10;
        $total = count($proveedoresData);
        $items = array_slice($proveedoresData, ($page - 1) * $perPage, $perPage);

        $proveedores = new LengthAwarePaginator(
            $items, $total, $perPage, $page, 
            ['path' => $request->url(), 'query' => $request->query()]
        );
        
        if ($request->ajax()) {
            return view('proveedores.table')->with('proveedores', $proveedores);
        }

        return view('proveedores.index')->with('proveedores', $proveedores);
    }
    
    public function create()
    {
        return view('proveedores.create');
    }
    
    public function store(Request $request)
    {
        $input = $request->all();
        
        $validacion = Validator::make($input, [
            'descripcion' => 'required|unique:proveedores,descripcion',
            'telefono' => 'required'
        ], [
            'descripcion.required' => 'El campo descripción es obligatorio.',
            'descripcion.unique' => 'Ya existe un proveedor con esta descripción.',
            'telefono.required' => 'El campo teléfono es obligatorio.'
        ]);
        
        if ($validacion->fails()) {
            return back()->withErrors($validacion)->withInput();
        }
        
        // CAMBIO 1: Insertar con estado TRUE
        DB::insert(
            'INSERT INTO proveedores (descripcion, direccion, telefono, estado) values (?, ?, ?, ?)',
            [
                strtoupper($input['descripcion']), 
                strtoupper($input['direccion'] ?? null), 
                $input['telefono'],
                true // Estado Activo
            ]
        );
            
        Alert::toast('Proveedor creado con éxito', 'success');
        return redirect()->route('proveedores.index');
    }

    public function edit($id)
    {
        $proveedor = DB::selectOne('SELECT * FROM proveedores WHERE id_proveedor = ?', [$id]);
        
        if(empty($proveedor)){
            Alert::toast('Proveedor no encontrado', 'error');
            return redirect()->route('proveedores.index');
        }

        // CAMBIO 2: Bloqueo de seguridad
        if (!$proveedor->estado) {
            Alert::warning('Acción Denegada', 'No se puede editar un proveedor inactivo.');
            return redirect()->route('proveedores.index');
        }
        
        return view('proveedores.edit')->with('proveedores', $proveedor);
    }
    
    public function update(Request $request, $id)
    {
        $proveedor = DB::selectOne('SELECT * FROM proveedores WHERE id_proveedor = ?', [$id]);
        
        if(empty($proveedor)){
            Alert::toast('Proveedor no encontrado', 'error');
            return redirect()->route('proveedores.index');
        }

        // CAMBIO 3: Validación estado
        if (!$proveedor->estado) {
            Alert::warning('Error', 'No se puede actualizar un proveedor inactivo.');
            return redirect()->route('proveedores.index');
        }
        
        $input = $request->all();
        
        $validacion = Validator::make($input, [
            'descripcion' => 'required|unique:proveedores,descripcion,' . $id . ',id_proveedor',
            'telefono' => 'required'
        ], [
            'descripcion.required' => 'El campo descripción es obligatorio.',
            'descripcion.unique' => 'Ya existe un proveedor con esta descripción.',
            'telefono.required' => 'El campo teléfono es obligatorio.'
        ]);
        
        if ($validacion->fails()) {
            return back()->withErrors($validacion)->withInput();
        }
        
        DB::update(
            'UPDATE proveedores SET descripcion = ?, direccion = ?, telefono = ? WHERE id_proveedor = ?', 
            [
                strtoupper($input['descripcion']), 
                strtoupper($input['direccion'] ?? null), 
                $input['telefono'], 
                $id
            ]
        );
        
        Alert::toast('Proveedor actualizado con éxito', 'success');
        return redirect()->route('proveedores.index');
    }

    /**
     * CAMBIO 4: Toggle de Estado
     */
    public function destroy($id)
    {
        // 1. Verificar existencia
        $proveedor = DB::selectOne('SELECT * FROM proveedores WHERE id_proveedor = ?', [$id]);
        
        if(empty($proveedor)){
            Alert::toast('Proveedor no encontrado', 'error');
            return redirect()->route('proveedores.index');
        }
        
        // 2. Invertir Estado
        DB::update('UPDATE proveedores SET estado = NOT estado WHERE id_proveedor = ?', [$id]);
        
        // 3. Mensaje dinámico
        $accion = $proveedor->estado ? 'inactivado' : 'activado';
        
        Alert::toast("Proveedor $accion con éxito", 'success');
        return redirect()->route('proveedores.index');
    }
}