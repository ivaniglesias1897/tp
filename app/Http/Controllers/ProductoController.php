<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Pagination\LengthAwarePaginator; 
use RealRashid\SweetAlert\Facades\Alert;

class ProductoController extends Controller
{
    private $path;

    public function __construct()
    {
        $this->middleware('auth');
        // Definimos la ruta de las imágenes
        $this->path = public_path() . "/img/productos/";
        
        $this->middleware('permission:productos index')->only(['index']);
        $this->middleware('permission:productos create')->only(['create', 'store']);
        $this->middleware('permission:productos edit')->only(['edit', 'update']);
        $this->middleware('permission:productos destroy')->only(['destroy']);
    }

    public function index(Request $request)
    {
        $buscar = trim($request->get('buscar'));
        
        // 1. Consulta Base: Traemos productos y unimos con marcas
        $sqlBase = 'SELECT p.*, m.descripcion as marcas 
                    FROM productos p
                    JOIN marcas m ON p.id_marca = m.id_marca';

        $sqlWhere = '';
        $params = [];
        
        if (!empty($buscar)) {

            // Detectamos si busca por PRECIO o ID (Numérico puro)
            if (is_numeric($buscar)) {
                $sqlWhere = " WHERE (
                    p.id_producto = ?              -- ID Exacto
                    OR p.precio = ?                -- Precio Exacto
                    OR p.descripcion ILIKE ?       -- Descripción Parcial
                    OR m.descripcion ILIKE ?       -- Marca Parcial
                )";
                
                $likeBuscar = '%' . $buscar . '%';
                $params = [$buscar, $buscar, $likeBuscar, $likeBuscar];

            } else {
                // Búsqueda de Texto
                $sqlWhere = " WHERE (
                    p.descripcion ILIKE ? 
                    OR m.descripcion ILIKE ?
                    -- Búsqueda por Estado
                    OR (CASE WHEN p.estado = true THEN 'Activo' ELSE 'Inactivo' END) ILIKE ?
                )";
                $likeBuscar = '%' . $buscar . '%';
                $params = [$likeBuscar, $likeBuscar, $likeBuscar];
            }
        }
        
        $productosData = DB::select(
            $sqlBase . $sqlWhere . ' ORDER BY p.id_producto DESC',
            $params
        );

        // Paginación Manual
        $page = $request->input('page', 1); 
        $perPage = 10; 
        $total = count($productosData); 
        $items = array_slice($productosData, ($page - 1) * $perPage, $perPage);

        $productos = new LengthAwarePaginator(
            $items, $total, $perPage, $page, 
            ['path' => $request->url(), 'query' => $request->query()]
        );
        
        if ($request->ajax()) { 
            return view('productos.table')->with('productos', $productos);
        }

        return view('productos.index')->with('productos', $productos);
    }

    public function create()
    {
        $tipo_iva = [
            '0' => 'Exento',
            '5' => 'Gravada 5%',
            '10' => 'Gravada 10%',
        ];

        // CAMBIO 1: Solo Marcas ACTIVAS
        $marcas = DB::table('marcas')
                    ->where('estado', true)
                    ->pluck('descripcion', 'id_marca');

        return view('productos.create')->with('tipo_iva', $tipo_iva)->with('marcas', $marcas);
    }

    public function store(Request $request)
    {
        $input = $request->all();

        // =========================================================================
        // CORRECCIÓN PRINCIPAL (EL APRENDIZAJE):
        // =========================================================================
        // El error "must be a number" ocurría porque el validador se ejecutaba 
        // ANTES de que quitaras los puntos. Para Laravel "6.767.000" es texto.
        //
        // SOLUCIÓN: Movemos la limpieza del precio AQUÍ ARRIBA, antes del Validator.
        // Modificamos el array $input directamente.
        // =========================================================================
        
        if (isset($input['precio'])) {
            // Reemplazamos el punto por vacío. Ej: "6.767.000" se convierte en "6767000"
            $input['precio'] = str_replace('.', '', $input['precio']);
        }

        // Ahora pasamos el $input (con el precio ya limpio) al validador
        $validacion = Validator::make($input, [
            'descripcion' => 'required|unique:productos,descripcion',
            
            // AHORA SÍ funcionará 'numeric', porque $input['precio'] ya es un número puro
            'precio' => 'required|numeric|min:0', 
            
            'id_marca' => 'required|exists:marcas,id_marca',
            'tipo_iva' => 'required|numeric|in:0,5,10',
            'imagen_producto' => 'nullable|image|max:2048' 
        ], [
            'descripcion.unique' => 'Ya existe un producto con esta descripción.',
            'id_marca.required' => 'La marca es obligatoria.',
            // Consejo: Es bueno agregar un mensaje personalizado para el precio
            'precio.numeric' => 'El precio debe ser un número válido.',
        ]);

        if ($validacion->fails()) {
            return redirect()->back()->withErrors($validacion)->withInput();
        }

        $imagen = null;
        if ($request->hasFile('imagen_producto')) {
            $imagen = time() . '_' . $request->file('imagen_producto')->getClientOriginalName();
            $request->file('imagen_producto')->move($this->path, $imagen);
        }

        // =========================================================================
        // CAMBIO EN LA INSERCIÓN:
        // =========================================================================
        // Antes hacías el str_replace aquí abajo. Ya lo borramos porque lo hicimos arriba.
        // Ahora simplemente usamos $input['precio'] que ya viene limpio y validado.
        // =========================================================================

        DB::insert(
            'INSERT INTO productos (descripcion, precio, id_marca, tipo_iva, imagen_producto, estado) VALUES (?, ?, ?, ?, ?, ?)',
            [
                strtoupper($input['descripcion']), 
                $input['precio'], // <--- Usamos el valor limpio del array
                $input['id_marca'], 
                $input['tipo_iva'], 
                $imagen,
                true // Estado Activo
            ]
        );

        Alert::toast('Producto creado correctamente.', 'success');
        return redirect(route('productos.index'));
    }

    public function edit($id)
    {
        $producto = DB::selectOne('SELECT * FROM productos WHERE id_producto = ?', [$id]);

        if (empty($producto)) {
            Alert::toast('Producto no encontrado.', 'error');
            return redirect(route('productos.index'));
        }

        // CAMBIO 3: Bloqueo de Seguridad
        if (!$producto->estado) {
            Alert::warning('Acción Denegada', 'No se puede editar un producto inactivo.');
            return redirect()->route('productos.index');
        }

        $tipo_iva = [
            '0' => 'Exento',
            '5' => 'Gravada 5%',
            '10' => 'Gravada 10%',
        ];

        // CAMBIO 4: Solo Marcas Activas
        $marcas = DB::table('marcas')
                    ->where('estado', true)
                    ->pluck('descripcion', 'id_marca');

        // Nota: paso variable 'productos' (plural) porque así lo espera tu vista edit
        return view('productos.edit')
                ->with('productos', $producto)
                ->with('tipo_iva', $tipo_iva)
                ->with('marcas', $marcas);
    }

    public function update(Request $request, $id) 
    {
        $input = $request->all();
        $producto = DB::selectOne('SELECT * FROM productos WHERE id_producto = ?', [$id]);

        if (empty($producto)) {
            Alert::toast('Producto no encontrado.', 'error');
            return redirect(route('productos.index'));
        }

        // CAMBIO 5: Validación de Estado
        if (!$producto->estado) {
            Alert::warning('Error', 'No se puede actualizar un producto inactivo.');
            return redirect()->route('productos.index');
        }

        $validacion = Validator::make($input, [
            'descripcion' => 'required|unique:productos,descripcion,' . $id . ',id_producto',
            'precio' => 'required|numeric|min:0',
            'id_marca' => 'required|exists:marcas,id_marca',
            'tipo_iva' => 'required|numeric|in:0,5,10',
            'imagen_producto' => 'nullable|image|max:2048', 
        ]);

        if ($validacion->fails()) {
            return redirect()->back()->withErrors($validacion)->withInput();
        }

        $imagen = $producto->imagen_producto;
        if ($request->hasFile('imagen_producto')) {
            // Opcional: Podrías borrar la imagen anterior aquí si quisieras limpiar disco
            // if($imagen && file_exists($this->path . $imagen)) unlink($this->path . $imagen);

            $imagen = time() . '_' . $request->file('imagen_producto')->getClientOriginalName();
            $request->file('imagen_producto')->move($this->path, $imagen);
        }

        $precio = str_replace('.', '', $input['precio']);

        DB::update(
            'UPDATE productos SET descripcion = ?, precio = ?, id_marca = ?, tipo_iva = ?, imagen_producto = ? WHERE id_producto = ?',
            [
                strtoupper($input['descripcion']), 
                $precio, 
                $input['id_marca'], 
                $input['tipo_iva'], 
                $imagen, 
                $id
            ]
        );

        Alert::toast('Producto actualizado correctamente.', 'success');
        return redirect(route('productos.index'));
    }

    /**
     * CAMBIO 6: Toggle de Estado (Activar/Inactivar)
     * Ya NO borramos físicamente ni la imagen ni el registro.
     */
    public function destroy($id) 
    {
        // 1. Verificar existencia
        $producto = DB::selectOne('SELECT * FROM productos WHERE id_producto = ?', [$id]);

        if (empty($producto)) {
            Alert::toast('Producto no encontrado.', 'error');
            return redirect(route('productos.index'));
        }

        // 2. Invertir Estado (SQL puro)
        DB::update('UPDATE productos SET estado = NOT estado WHERE id_producto = ?', [$id]);
        
        // 3. Mensaje dinámico
        $accion = $producto->estado ? 'inactivado' : 'activado';

        Alert::toast("Producto $accion correctamente.", 'success');
        return redirect(route('productos.index'));
    }
}