<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use RealRashid\SweetAlert\Facades\Alert;

class MarcaController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('permission:marcas index')->only(['index']);
        $this->middleware('permission:marcas create')->only(['create', 'store']);
        $this->middleware('permission:marcas edit')->only(['edit', 'update']);
        $this->middleware('permission:marcas destroy')->only(['destroy']);
    }

    public function index(Request $request)
    {
        $buscar = trim($request->get('buscar'));
        
        $sqlBase = 'SELECT * FROM marcas';
        $sqlWhere = '';
        $bindings = [];
        $sqlOrder = 'ORDER BY id_marca ASC';

        if (!empty($buscar)) {
            if (ctype_digit($buscar) && strlen($buscar) <= 4) {
                $sqlWhere = "WHERE id_marca = ?";
                $bindings = [(int)$buscar];
            } else {
                $like = '%' . $buscar . '%';
                $sqlWhere = "WHERE (descripcion ILIKE ? OR id_marca::text ILIKE ?)";
                $bindings = [$like, $like];
            }
        }
        
        $marcasData = DB::select($sqlBase . ' ' . $sqlWhere . ' ' . $sqlOrder, $bindings);
        
        $page = $request->input('page', 1);
        $perPage = 10;
        $total = count($marcasData);
        $items = array_slice($marcasData, ($page - 1) * $perPage, $perPage);

        $marcas = new LengthAwarePaginator(
            $items, $total, $perPage, $page, 
            ['path' => $request->url(), 'query' => $request->query()]
        );
        
        if ($request->ajax()) {
            return view('marcas.table')->with('marcas', $marcas);
        }

        return view('marcas.index')->with('marcas', $marcas);
    }
    
    public function create()
    {
        return view('marcas.create');
    }

    public function store(Request $request)
    {
        $input = $request->all();
        $input['descripcion'] = strtoupper(trim($input['descripcion']));

        $validacion = Validator::make($input, [
            'descripcion' => 'required|unique:marcas,descripcion'
        ], [
            'descripcion.required' => 'El campo descripción es obligatorio.',
            'descripcion.unique' => 'Esta marca ya existe.'
        ]);
        
        if ($validacion->fails()) {
            return back()->withErrors($validacion)->withInput();
        }

        // CAMBIO 1: Insertar con estado TRUE
        DB::insert(
            'INSERT INTO marcas (descripcion, estado) values (?, ?)',
            [$input['descripcion'], true]
        );
        
        Alert::toast('Marca registrada con éxito', 'success');
        return redirect()->route('marcas.index');
    }

    public function edit($id)
    {
        $marca = DB::selectOne('SELECT * FROM marcas WHERE id_marca = ?', [$id]);
        
        if (empty($marca)) {
            Alert::toast('Marca no encontrada', 'error');
            return redirect()->route('marcas.index');
        }

        // CAMBIO 2: Blindaje de seguridad
        if (!$marca->estado) {
            Alert::warning('Acción Denegada', 'No se puede editar una marca inactiva.');
            return redirect()->route('marcas.index');
        }
        
        return view('marcas.edit')->with('marcas', $marca);
    }

    public function update(Request $request, $id)
    {
        $marca = DB::selectOne('SELECT * FROM marcas WHERE id_marca = ?', [$id]);
        
        if (empty($marca)) {
            Alert::toast('Marca no encontrada', 'error');
            return redirect()->route('marcas.index');
        }

        // CAMBIO 3: Validación antes de update
        if (!$marca->estado) {
            Alert::warning('Error', 'No se puede actualizar una marca inactiva.');
            return redirect()->route('marcas.index');
        }
        
        $input = $request->all();
        $input['descripcion'] = strtoupper(trim($input['descripcion']));

        $validacion = Validator::make($input, [
            'descripcion' => 'required|unique:marcas,descripcion,' . $id . ',id_marca'
        ], [
            'descripcion.required' => 'El campo descripción es obligatorio.',
            'descripcion.unique' => 'Esta marca ya existe.'
        ]);
        
        if ($validacion->fails()) {
            return back()->withErrors($validacion)->withInput();
        }

        DB::update(
            'UPDATE marcas SET descripcion = ? WHERE id_marca = ?',
            [$input['descripcion'], $id]
        );
        
        Alert::toast('Marca actualizada con éxito', 'success');
        return redirect()->route('marcas.index');
    }
    
    /**
     * CAMBIO 4: Lógica de Toggle (Activación/Desactivación)
     */
    public function destroy($id)
    {
        // 1. Verificar existencia
        $marca = DB::selectOne('SELECT * FROM marcas WHERE id_marca = ?', [$id]);
        
        if (empty($marca)) {
            Alert::toast('Marca no encontrada', 'error');
            return redirect()->route('marcas.index');
        }
        
        // 2. Invertir Estado
        DB::update('UPDATE marcas SET estado = NOT estado WHERE id_marca = ?', [$id]);
        
        // 3. Mensaje dinámico (Femenino)
        $accion = $marca->estado ? 'inactivada' : 'activada';
        
        Alert::toast("Marca $accion con éxito", 'success');
        return redirect()->route('marcas.index');
    }
}