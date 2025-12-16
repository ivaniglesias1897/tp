<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use RealRashid\SweetAlert\Facades\Alert;

class CiudadController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('permission:ciudades index')->only(['index']);
        $this->middleware('permission:ciudades create')->only(['create', 'store']);
        $this->middleware('permission:ciudades edit')->only(['edit', 'update']);
        $this->middleware('permission:ciudades destroy')->only(['destroy']);
    }

    public function index(Request $request)
    {
        $buscar = trim($request->get('buscar'));
        
        $sqlBase = 'SELECT c.*, d.descripcion as departamento
                    FROM ciudades c
                    JOIN departamentos d ON c.id_departamento = d.id_departamento';
        
        $sqlWhere = '';
        $bindings = [];
        $sqlOrder = 'ORDER BY c.id_ciudad ASC';

        if (!empty($buscar)) {
            if (ctype_digit($buscar) && strlen($buscar) <= 4) {
                $sqlWhere = "WHERE c.id_ciudad = ?";
                $bindings = [(int)$buscar];
            } else {
                $like = '%' . $buscar . '%';
                $sqlWhere = "WHERE (
                    c.descripcion ILIKE ? 
                    OR d.descripcion ILIKE ? 
                    OR c.id_ciudad::text ILIKE ?
                )";
                $bindings = [$like, $like, $like];
            }
        }
        
        $ciudadesData = DB::select($sqlBase . ' ' . $sqlWhere . ' ' . $sqlOrder, $bindings);
        
        $page = $request->input('page', 1);
        $perPage = 10;
        $total = count($ciudadesData);
        $items = array_slice($ciudadesData, ($page - 1) * $perPage, $perPage);

        $ciudades = new LengthAwarePaginator(
            $items, $total, $perPage, $page, 
            ['path' => $request->url(), 'query' => $request->query()]
        );
        
        if ($request->ajax()) {
            return view('ciudades.table')->with('ciudades', $ciudades);
        }
        
        return view('ciudades.index')->with('ciudades', $ciudades);
    }

    public function create()
    {
        // CAMBIO 1: Solo departamentos ACTIVOS
        $departamentos = DB::table('departamentos')
                           ->where('estado', true)
                           ->pluck('descripcion', 'id_departamento');
                           
        return view('ciudades.create')->with('departamentos', $departamentos);
    }

    public function store(Request $request)
    {
        $input = $request->all();
        $input['descripcion'] = strtoupper(trim($input['descripcion']));

        $validacion = Validator::make($input, [
            'descripcion' => 'required|unique:ciudades,descripcion',
            'id_departamento' => 'required|exists:departamentos,id_departamento'
        ], [
            'descripcion.required' => 'El campo descripción es obligatorio.',
            'descripcion.unique' => 'Esta ciudad ya existe.',
            'id_departamento.required' => 'El departamento es obligatorio.'
        ]);
        
        if ($validacion->fails()) {
            return back()->withErrors($validacion)->withInput();
        }

        // CAMBIO 2: Insertar con estado TRUE
        DB::insert(
            'INSERT INTO ciudades (descripcion, id_departamento, estado) VALUES (?, ?, ?)',
            [$input['descripcion'], $input['id_departamento'], true]
        );
        
        Alert::toast('Ciudad creada con éxito', 'success');
        return redirect()->route('ciudades.index');
    }

    public function edit($id)
    {
        $ciudad = DB::selectOne('SELECT * FROM ciudades WHERE id_ciudad = ?', [$id]);

        if (empty($ciudad)) {
            Alert::toast('Ciudad no encontrada', 'error');
            return redirect()->route('ciudades.index');
        }

        // CAMBIO 3: Bloqueo de seguridad
        if (!$ciudad->estado) {
            Alert::warning('Acción Denegada', 'No se puede editar una ciudad inactiva.');
            return redirect()->route('ciudades.index');
        }

        // CAMBIO 4: Departamentos activos
        $departamentos = DB::table('departamentos')
                           ->where('estado', true)
                           ->pluck('descripcion', 'id_departamento');

        return view('ciudades.edit')->with('ciudades', $ciudad)
            ->with('departamentos', $departamentos);
    }

    public function update(Request $request, $id)
    {
        $ciudad = DB::selectOne('SELECT * FROM ciudades WHERE id_ciudad = ?', [$id]);

        if (empty($ciudad)) {
            Alert::toast('Ciudad no encontrada', 'error');
            return redirect()->route('ciudades.index');
        }

        // CAMBIO 5: Validación estado
        if (!$ciudad->estado) {
            Alert::warning('Error', 'No se puede actualizar una ciudad inactiva.');
            return redirect()->route('ciudades.index');
        }

        $input = $request->all();
        $input['descripcion'] = strtoupper(trim($input['descripcion']));

        $validacion = Validator::make($input, [
            'descripcion' => 'required|unique:ciudades,descripcion,' . $id . ',id_ciudad',
            'id_departamento' => 'required|exists:departamentos,id_departamento'
        ]);
        
        if ($validacion->fails()) {
            return back()->withErrors($validacion)->withInput();
        }

        DB::update(
            'UPDATE ciudades SET descripcion = ?, id_departamento = ? WHERE id_ciudad = ?',
            [$input['descripcion'], $input['id_departamento'], $id]
        );
        
        Alert::toast('Ciudad actualizada con éxito', 'success');
        return redirect()->route('ciudades.index');
    }
    
    /**
     * CAMBIO 6: Toggle de Estado
     */
    public function destroy($id)
    {
        // 1. Verificar existencia
        $ciudad = DB::selectOne('SELECT * FROM ciudades WHERE id_ciudad = ?', [$id]);

        if (empty($ciudad)) {
            Alert::toast('Ciudad no encontrada', 'error');
            return redirect()->route('ciudades.index');
        }
        
        // 2. Invertir Estado
        DB::update('UPDATE ciudades SET estado = NOT estado WHERE id_ciudad = ?', [$id]);
        
        // 3. Mensaje dinámico
        $accion = $ciudad->estado ? 'inactivada' : 'activada';
        
        Alert::toast("Ciudad $accion con éxito", 'success');
        return redirect()->route('ciudades.index');
    }
}