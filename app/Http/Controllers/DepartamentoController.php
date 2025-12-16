<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use RealRashid\SweetAlert\Facades\Alert;
use Illuminate\Database\QueryException;

class DepartamentoController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('permission:departamentos index')->only(['index']);
        $this->middleware('permission:departamentos create')->only(['create', 'store']);
        $this->middleware('permission:departamentos edit')->only(['edit', 'update']);
        $this->middleware('permission:departamentos destroy')->only(['destroy']);
    }

    public function index(Request $request)
    {
        $buscar = trim($request->get('buscar'));
        
        $sqlBase = 'SELECT * FROM departamentos';
        $sqlWhere = '';
        $bindings = [];
        $sqlOrder = 'ORDER BY id_departamento ASC';

        if (!empty($buscar)) {
            if (ctype_digit($buscar) && strlen($buscar) <= 4) {
                $sqlWhere = "WHERE id_departamento = ?";
                $bindings = [(int)$buscar];
            } else {
                $like = '%' . $buscar . '%';
                $sqlWhere = "WHERE (descripcion ILIKE ? OR id_departamento::text ILIKE ?)";
                $bindings = [$like, $like];
            }
        }
        
        $departamentosData = DB::select($sqlBase . ' ' . $sqlWhere . ' ' . $sqlOrder, $bindings);
        
        $page = $request->input('page', 1);
        $perPage = 10;
        $total = count($departamentosData);
        $items = array_slice($departamentosData, ($page - 1) * $perPage, $perPage);

        $departamentos = new LengthAwarePaginator(
            $items, $total, $perPage, $page, 
            ['path' => $request->url(), 'query' => $request->query()]
        );
        
        if ($request->ajax()) {
            return view('departamentos.table')->with('departamentos', $departamentos);
        }

        return view('departamentos.index')->with('departamentos', $departamentos);
    }
    
    public function create()
    {
        return view('departamentos.create');
    }

    public function store(Request $request)
    {
        $input = $request->all();
        $input['descripcion'] = strtoupper(trim($input['descripcion'])); // Limpieza

        $validacion = Validator::make($input, [
            'descripcion' => 'required|unique:departamentos,descripcion',
        ], [
            'descripcion.required' => 'El campo descripción es obligatorio.',
            'descripcion.unique' => 'Este departamento ya existe.',
        ]);
        
        if ($validacion->fails()) {
            return back()->withErrors($validacion)->withInput();
        }

        // CAMBIO 1: Insertamos explícitamente 'estado' como true
        DB::insert(
            'INSERT INTO departamentos (descripcion, estado) VALUES (?, ?)',
            [$input['descripcion'], true]
        );
        
        Alert::toast('Departamento creado con éxito', 'success');
        return redirect()->route('departamentos.index');
    }

    public function edit($id)
    {
        $departamento = DB::selectOne('SELECT * FROM departamentos WHERE id_departamento = ?', [$id]);

        if (empty($departamento)) {
            Alert::toast('Departamento no encontrado', 'error');
            return redirect()->route('departamentos.index');
        }

        // CAMBIO 2: Blindaje de Seguridad. No editar inactivos.
        if (!$departamento->estado) {
            Alert::warning('Acción Denegada', 'No se puede editar un departamento inactivo.');
            return redirect()->route('departamentos.index');
        }

        return view('departamentos.edit')->with('departamento', $departamento);
    }

    public function update(Request $request, $id)
    {
        // 1. Validar existencia
        $departamento = DB::selectOne('SELECT * FROM departamentos WHERE id_departamento = ?', [$id]);

        if (empty($departamento)) {
            Alert::toast('Departamento no encontrado', 'error');
            return redirect()->route('departamentos.index');
        }

        // CAMBIO 3: Validación de estado antes de actualizar
        if (!$departamento->estado) {
            Alert::warning('Error', 'No se puede actualizar un departamento inactivo.');
            return redirect()->route('departamentos.index');
        }

        $input = $request->all();
        $input['descripcion'] = strtoupper(trim($input['descripcion'])); // Limpieza

        $validacion = Validator::make($input, [
            'descripcion' => 'required|unique:departamentos,descripcion,' . $id . ',id_departamento'
        ], [
            'descripcion.required' => 'El campo descripción es obligatorio.',
            'descripcion.unique' => 'Este departamento ya existe.'
        ]);
        
        if ($validacion->fails()) {
            return back()->withErrors($validacion)->withInput();
        }

        DB::update(
            'UPDATE departamentos SET descripcion = ? WHERE id_departamento = ?',
            [$input['descripcion'], $id]
        );
        
        Alert::toast('Departamento actualizado con éxito', 'success');
        return redirect()->route('departamentos.index');
    }
    
    /**
     * CAMBIO 4: destroy ahora es TOGGLE (Activar/Inactivar).
     * Eliminamos el try-catch de FK porque ya no borramos físicamente.
     */
    public function destroy($id)
    {
        // 1. Verificar existencia
        $departamento = DB::selectOne('SELECT * FROM departamentos WHERE id_departamento = ?', [$id]);

        if (empty($departamento)) {
            Alert::toast('Departamento no encontrado', 'error');
            return redirect()->route('departamentos.index');
        }
        
        // 2. LOGICA UPDATE (NOT estado)
        DB::update('UPDATE departamentos SET estado = NOT estado WHERE id_departamento = ?', [$id]);
        
        // 3. Mensaje dinámico
        $accion = $departamento->estado ? 'inactivado' : 'activado';
        
        Alert::toast("Departamento $accion con éxito", 'success');
        return redirect()->route('departamentos.index');
    }
}