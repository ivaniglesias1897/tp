<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use RealRashid\SweetAlert\Facades\Alert;

class PermissionController extends Controller
{
    public function index(Request $request)
    {
        $buscar = $request->get('buscar');
        if ($buscar) {
            $permisos = DB::select(
                'SELECT * FROM permissions WHERE name ILIKE ?',
                ['%' . $buscar . '%']
            );
        } else {
            $permisos = DB::select('SELECT * FROM permissions');
        }
        //Definimos los valores de paginación
        $page = $request->input('page', 1);   // página actual (por defecto 1)
        $perPage = 10;                        // cantidad de registros por página
        $total = count($permisos);            // total de registros
        //Cortamos el array para solo devolver los registros de la página actual
        $items = array_slice($permisos, ($page - 1) * $perPage, $perPage);
        //Creamos el paginador manualmente
        $permisos = new LengthAwarePaginator(
            $items,        // registros de esta página
            $total,        // total de registros
            $perPage,      // registros por página
            $page,         // página actual
            [
                'path'  => $request->url(),     // mantiene la ruta base
                'query' => $request->query(),   // mantiene parámetros como "buscar"
            ]
        );
        // si la accion es buscardor entonces significa que se debe recargar mediante ajax la tabla
        if ($request->ajax()) {
            //solo llmamamos a table.blade.php y mediante compact pasamos la variable users
            return view('permissions.table')->with('permisos', $permisos);
        }
        return view('permissions.index')->with('permisos', $permisos);
    }
    public function create()
    {
        return view('permissions.create');
    }
    public function store(Request $request)
    {
        $input = $request->all();
        //Validar qu el campo 'name' no este vacio
        $validacion = Validator::make($input, [
            'name' => 'required|unique:permissions,name',
        ], 
        [
            'name.required' => 'El campo nombre es obligatorio.',
            'name.unique' => 'El nombre del permiso ya existe.',
        ]);

        //Si la validacion falla redirigir de vuelta con errores
        if ($validacion->fails()) {
            return redirect()->back()->withErrors($validacion)->withInput();
        }
        //Insertar el nuevo permiso en la base de datos
        DB::insert('INSERT INTO permissions (name, guard_name) VALUES (?, ?)', [
            $input['name'],
            $input['guard_name']
        ]);
         //Mostrar un mensaje de configuracion
        Alert::toast('Permiso creado con exito', 'success');
        //Redirigir al listado de permisos
        return redirect()->route('permissions.index');
    }
    public function edit($id)
    {
        $permisos = DB::selectone('select * from permissions where id = ?', [$id]);
        //verificar si se encontro el permiso
        if (empty($permisos)) {
            Alert::toast('El permiso no existe', 'error');
            return redirect()->route('permissions.index');
        }
        return view('permissions.edit')->with('permisos', $permisos);
    }
    public function update(Request $request, $id)
    {
        $input = $request->all();
        $permisos = DB::selectone('select * from permissions where id = ?', [$id]);
        //verificar si se encontro el permiso
        if (empty($permisos)) {
            Alert::toast('El permiso no existe', 'error');
            return redirect()->route('permissions.index');
        }   
        //Validar que el campo 'name' no este vacio
        $validacion = Validator::make($input, [
            'name' => 'required|unique:permissions,name,' . $id,
        ], 
        [
            'name.required' => 'El campo nombre es obligatorio.',
            'name.unique' => 'El nombre del permiso ya existe.',
        ]);

        //Si la validacion falla redirigir de vuelta con errores
        if ($validacion->fails()) {
            return redirect()->back()->withErrors($validacion)->withInput();
        }
        //Actualizar el permiso en la base de datos
        DB::update('UPDATE permissions SET name = ?, guard_name = ? WHERE id = ?', [
            $input['name'],
            $input['guard_name'],
            $id
        ]);
        //Mostrar un mensaje de configuracion
        Alert::toast('Permiso actualizado con exito', 'success');

        //Redirigir al listado de permisos
        return redirect()->route('permissions.index');
    }
    public function destroy($id)
    {
        $permisos = DB::selectone('select * from permissions where id = ?', [$id]);
        //verificar si se encontro el permiso
        if (empty($permisos)) {
            Alert::toast('El permiso no existe', 'error');
            return redirect()->route('permissions.index');
        }
        //Utilizar un bloque Try-catch para manejar posibles errores al eliminar
        try {
            //Verificar si el permiso esta asignado a algun rol
           DB::delete('DELETE FROM permissions WHERE id = ?', [$id]);
        } catch (\Exception $e) {
            //Manejar el error si el permiso esta asignado a algun rol
            Alert::toast('Error al eliminar el permiso: ' . $e->getMessage(), 'error');
            return redirect()->route('permissions.index');
        }   

        //Mostrar un mensaje de confirmacion
        Alert::toast('Permiso eliminado con exito', 'success');

        //Redirigir al listado de permisos
        return redirect()->route('permissions.index');
    }
}
