<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use RealRashid\SweetAlert\Facades\Alert;
use Illuminate\Database\QueryException; // Importante para capturar errores SQL

class CargoController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('permission:cargos index')->only(['index']);
        $this->middleware('permission:cargos create')->only(['create', 'store']);
        $this->middleware('permission:cargos edit')->only(['edit', 'update']);
        $this->middleware('permission:cargos destroy')->only(['destroy']);
    }

    /**
     * Lista de cargos con búsqueda inteligente por ID y Descripción.
     */
    public function index(Request $request)
    {
        $buscar = trim($request->get('buscar'));
        
        // 1. Consulta Base
        $sqlBase = 'SELECT * FROM cargos';
        
        $sqlWhere = '';
        $bindings = [];
        $sqlOrder = 'ORDER BY id_cargo ASC'; // Orden por defecto

        if (!empty($buscar)) {
            
            // CASO A: Búsqueda por ID exacto (Si es número y corto)
            if (ctype_digit($buscar) && strlen($buscar) <= 4) {
                
                $sqlWhere = "WHERE id_cargo = ?";
                $bindings = [(int)$buscar];
                // Mantenemos orden ASC por ID

            } else {
                
                // CASO B: Búsqueda General (Texto)
                $like = '%' . $buscar . '%';
                
                $sqlWhere = "WHERE (
                    descripcion ILIKE ? 
                    OR id_cargo::text ILIKE ?  -- Búsqueda por ID como texto
                )";
                
                // 2 parámetros para los 2 signos de interrogación (?)
                $bindings = [
                    $like, // descripcion
                    $like  // id_cargo
                ];
            }
        }
        
        // 2. Ejecutar Consulta
        $cargos = DB::select($sqlBase . ' ' . $sqlWhere . ' ' . $sqlOrder, $bindings);
        
        // 3. Paginación Manual
        $page = $request->input('page', 1);
        $perPage = 10;
        $total = count($cargos);
        $items = array_slice($cargos, ($page - 1) * $perPage, $perPage);

        $cargos = new LengthAwarePaginator(
            $items, 
            $total, 
            $perPage, 
            $page, 
            ['path' => $request->url(), 'query' => $request->query()]
        );
        
        // Adjuntamos parámetros para mantener la búsqueda
        $cargos->appends($request->query()); 
        
        if ($request->ajax()) {
            return view('cargos.table')->with('cargos', $cargos);
        }

        return view('cargos.index')->with('cargos', $cargos);
    }


    public function create()
    {
        return view('cargos.create');
    }
    
    public function store(Request $request)
    {
        $input = $request->all();

        // Validación: Descripción única
        $validacion = Validator::make($input, [
            'descripcion' => 'required|unique:cargos,descripcion',
        ], [
            'descripcion.required' => 'El campo descripción es obligatorio.',
            'descripcion.unique'   => 'Este cargo ya existe.',
        ]);

        if ($validacion->fails()) {
            return back()->withErrors($validacion)->withInput();
        }

        DB::insert('INSERT INTO cargos (descripcion) VALUES (?)', 
            [ 
                strtoupper($input['descripcion']) // Estandarizamos a mayúsculas
            ]
        );

        Alert::toast('El cargo fue creado con éxito.', 'success');
        return redirect(route('cargos.index'));
    }

    public function edit($id)
    {
        $cargo = DB::selectOne('SELECT * FROM cargos WHERE id_cargo = ?', [$id]);

        if (empty($cargo)) {
            Alert::toast('El cargo no fue encontrado.', 'error');
            return redirect()->route('cargos.index');
        }

        return view('cargos.edit')->with('cargo', $cargo);
    }

    public function update(Request $request, $id)
    {
        $cargo = DB::selectOne('SELECT * FROM cargos WHERE id_cargo = ?', [$id]);
        
        if (empty($cargo)) {
            Alert::toast('El cargo no fue encontrado.', 'error');
            return redirect()->route('cargos.index');
        }
        
        $input = $request->all();

        // Validación: Único ignorando ID actual
        $validacion = Validator::make($input, [
            'descripcion' => 'required|unique:cargos,descripcion,' . $id . ',id_cargo',
        ], [
            'descripcion.required' => 'El campo descripción es obligatorio.',
            'descripcion.unique'   => 'Este cargo ya existe.',
        ]);

        if ($validacion->fails()) {
            return back()->withErrors($validacion)->withInput();
        }

        DB::update('UPDATE cargos SET descripcion = ? WHERE id_cargo = ?', 
            [
                strtoupper($input['descripcion']),
                $id
            ]
        );

        Alert::toast('El cargo fue actualizado con éxito.', 'success');
        return redirect(route('cargos.index'));
    }

    /**
     * Elimina un cargo controlando integridad referencial.
     */
    public function destroy($id)
    {
        // 1. Verificar existencia
        $cargo = DB::selectOne('SELECT * FROM cargos WHERE id_cargo = ?', [$id]);

        if (empty($cargo)) {
            Alert::toast('El cargo no fue encontrado.', 'error');
            return redirect()->route('cargos.index');
        }

        // 2. EL CAMBIO MAESTRO: UPDATE en lugar de DELETE
        // Usamos SQL puro para invertir el booleano.
        DB::update('UPDATE cargos SET estado = NOT estado WHERE id_cargo = ?', [$id]);  

        // 3. Generar mensaje dinámico para el usuario
        // Si el estado original era TRUE, ahora es FALSE (se inactivó).
        // Si el estado original era FALSE, ahora es TRUE (se activó).
        $mensaje = $cargo->estado ? 'Cargo inactivado correctamente.' : 'Cargo activado correctamente.';

        Alert::toast($mensaje, 'success');
        return redirect(route('cargos.index'));
    }
}