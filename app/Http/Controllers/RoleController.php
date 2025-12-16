<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use RealRashid\SweetAlert\Facades\Alert;
use Spatie\Permission\Models\Role;
use Illuminate\Database\QueryException; // Importante para capturar errores SQL

class RoleController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('permission:roles index')->only(['index']);
        $this->middleware('permission:roles create')->only(['create', 'store']);
        $this->middleware('permission:roles edit')->only(['edit', 'update']);
        $this->middleware('permission:roles destroy')->only(['destroy']);
    }

    /**
     * Lista de roles con búsqueda inteligente por ID, Name y Guard Name.
     */
    public function index(Request $request)
    {
        $buscar = trim($request->get('buscar'));
        
        // 1. Consulta Base
        $sqlBase = 'SELECT * FROM roles';
        
        $sqlWhere = '';
        $bindings = [];
        $sqlOrder = 'ORDER BY id ASC'; // Orden por defecto

        if (!empty($buscar)) {
            
            // CASO A: Búsqueda por ID exacto (Si es número y corto)
            if (ctype_digit($buscar) && strlen($buscar) <= 4) {
                
                $sqlWhere = "WHERE id = ?";
                $bindings = [(int)$buscar];
                // Mantenemos orden ASC por ID

            } else {
                
                // CASO B: Búsqueda General (Texto)
                $like = '%' . $buscar . '%';
                
                $sqlWhere = "WHERE (
                    name ILIKE ? 
                    OR guard_name ILIKE ? 
                    OR CAST(id AS TEXT) ILIKE ?  -- Búsqueda por ID como texto
                )";
                
                // 3 parámetros para los 3 signos de interrogación (?)
                $bindings = [
                    $like, // name
                    $like, // guard_name
                    $like  // id
                ];
            }
        }
        
        // 2. Ejecutar Consulta
        $roles = DB::select($sqlBase . ' ' . $sqlWhere . ' ' . $sqlOrder, $bindings);

        // 3. Paginación Manual
        $page = $request->input('page', 1);
        $perPage = 10;
        $total = count($roles);
        $items = array_slice($roles, ($page - 1) * $perPage, $perPage);

        $roles = new LengthAwarePaginator(
            $items, 
            $total, 
            $perPage, 
            $page, 
            ['path' => $request->url(), 'query' => $request->query()]
        ); 
        
        // Adjuntamos parámetros para mantener la búsqueda
        $roles->appends($request->query());

        if ($request->ajax()) {
            return view('roles.table')->with('roles', $roles);
        }
        
        return view('roles.index')->with('roles', $roles);
    }

    public function create()
    {
        $permisos = DB::table('permissions')->get();
        return view('roles.create')->with('permisos', $permisos);
    }

    public function store(Request $request)
    {
        $input = $request->all();
        $input['guard_name'] = 'web';

        $permissions = $request->input('permiso_id', []);

        $validateData = Validator::make($input, [
            'name' => 'required|unique:roles,name',
        ], [
            'name.required' => 'El campo nombre es obligatorio',
            'name.unique' => 'El nombre del rol ya existe'
        ]);
        
        if ($validateData->fails()) {
            return redirect()->back()->withErrors($validateData)->withInput();
        }

        $role_id = DB::table('roles')->insertGetId([
            'name' => $input['name'],
            'guard_name' => $input['guard_name']
        ]);

        $role = Role::find($role_id);
        $role->permissions()->sync($permissions);
        
        Artisan::call('optimize:clear');

        Alert::toast('Rol creado correctamente', 'success');
        return redirect()->route('roles.index');
    }

    public function edit($id)
    {
        $role = Role::find($id);

        if (empty($role)) {
            Alert::toast('Rol no encontrado', 'error');
            return redirect()->route('roles.index');
        }
        
        $permisos = DB::table('permissions')->get();
        $rolePermissions = DB::select('SELECT permission_id FROM role_has_permissions WHERE role_id = ?', [$id]);

        $rolePermissionIds = [];
        foreach ($rolePermissions as $permission) {
            $rolePermissionIds[] = $permission->permission_id;
        }

        return view('roles.edit')->with('roles', $role)
            ->with('permisos', $permisos)
            ->with('rolePermissionIds', $rolePermissionIds);
    }

    public function update(Request $request, $id)
    {
        $input = $request->all();
        $input['guard_name'] = 'web';

        $permissions = $request->get('permiso_id', []);

        $validateData = Validator::make($input, [
            'name' => 'required|unique:roles,name,' . $id,
        ], [
            'name.required' => 'El campo nombre es obligatorio',
            'name.unique' => 'El nombre del rol ya existe'
        ]);

        if ($validateData->fails()) {
            return redirect()->back()->withErrors($validateData)->withInput();
        }

        DB::update('UPDATE roles SET name = ?, guard_name = ? WHERE id = ?', [
            $input['name'],
            $input['guard_name'],
            $id
        ]);

        $role = Role::find($id);
        $role->permissions()->sync($permissions);
        
        Artisan::call('optimize:clear');

        Alert::toast('Rol actualizado correctamente', 'success');
        return redirect()->route('roles.index');
    }

    /**
     * Elimina un rol controlando que no tenga usuarios asignados.
     */
    public function destroy($id)
    {
        try {
            // 1. Verificar existencia
            $role = DB::selectOne('SELECT * FROM roles WHERE id = ?', [$id]);
            
            if (empty($role)) {
                Alert::toast('Rol no encontrado', 'error');
                return redirect()->route('roles.index');
            }

            // 2. Intentar Eliminar
            // NOTA: Si borramos directo de la tabla roles, la FK en 'model_has_roles' (usuarios) saltará si hay uso.
            DB::delete('DELETE FROM roles WHERE id = ?', [$id]);
            
            Alert::toast('Rol eliminado correctamente', 'success');
            return redirect()->route('roles.index');

        } catch (QueryException $e) {
            
            // 3. Capturar Error de Llave Foránea (Código 23503)
            // Esto ocurre si el rol está asignado a un USUARIO (model_has_roles) o tiene PERMISOS (role_has_permissions)
            if ($e->getCode() == '23503') {
                Alert::error('No se puede eliminar', 'Este rol está asignado a Usuarios o tiene Permisos vinculados. No es posible eliminarlo.');
                return redirect()->route('roles.index');
            }

            // 4. Capturar otros errores
            Alert::error('Error', 'Ocurrió un error inesperado al intentar eliminar el rol.');
            return redirect()->route('roles.index');
        }
    }
}