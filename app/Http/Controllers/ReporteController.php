<?php

namespace App\Http\Controllers;

use App\Exports\CargoExport;
use App\Exports\ProveedoresExport;
use App\Exports\ProductosExport;
use App\Exports\SucursalesExport;
use App\Exports\VentasExport;
use App\Exports\ComprasExport;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

class ReporteController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth'); // Asegura que el usuario esté autenticado
        $this->middleware('permission:reporte-cargos')->only(['rpt_cargos']);
        $this->middleware('permission:reporte-clientes')->only(['rpt_clientes']);
        $this->middleware('permission:reporte-ventas')->only(['rpt_ventas']);
        $this->middleware('permission:reporte-compras')->only(['rpt_compras']);
        $this->middleware('permission:reporte-proveedores')->only(['rpt_proveedores']);
        $this->middleware('permission:reporte-productos')->only(['rpt_productos']);
        $this->middleware('permission:reporte-sucursales')->only(['rpt_sucursales']);
    }
    public function rpt_cargos(Request $request)
    {
        // recibir datos del formulario a partir de la url get
        $input = $request->all();

        // verificar si los filtros no estan vacios para realizar el where en la consulta cargos
        if (!empty($input['desde']) and !empty($input['hasta'])) {
            $cargos = DB::select('SELECT * FROM cargos WHERE id_cargo 
            BETWEEN ' . $input['desde'] . ' AND ' . $input['hasta']);
        } else {
            // si no se reciben datos, se traen todos los registros (SELECT * FROM cargos)
            $cargos = DB::select('SELECT * FROM cargos');
        }

        if (isset($input['exportar']) && $input['exportar'] == 'pdf') {
            ##crear vista pdf con loadView y utilizar la misma vista reportes rpt_cargos para convertir en pdf
            $pdf = Pdf::loadView(
                'reportes.pdf_cargos',
                compact('cargos')
            )
                ->setPaper('a4', 'portrait'); ## especificar tamaño de hoja y disposición
            # de hoja landscape=horizontal, portrait=vertical

            ##retornar pdf con una configuracion de pagina tipo de impresion y que se hara una descarga
            return $pdf->download("ReporteCargos.pdf");
        } elseif (isset($input['exportar']) && $input['exportar'] == 'excel') {
            // Verificar que se pasen los parámetros correctamente
            $desde = isset($input['desde']) ? $input['desde'] : null;
            $hasta = isset($input['hasta']) ? $input['hasta'] : null;

            // llamar la funcion exportar Excel
            return Excel::download(new CargoExport($desde, $hasta), 'cargos.xlsx');
        }


        return view('reportes.rpt_cargos')->with('cargos', $cargos);
    }

    public function rpt_clientes(Request $request)
    {
        // recibir datos del formulario a partir de la url get
        $input = $request->all();

        // definir variables para filtros
        $filtro_ciudad = "";
        $filtro_desde = "";
        $filtro_hasta = "";

        if (!empty($input['ciudad'])) {
            // concatenar los filtros y siempre dejar un espacio en blanco al comienzo del string " "
            $filtro_ciudad = " AND c.id_ciudad = " . $input['ciudad'];
        }

        // filtros desde y concatena el sql and
        if (!empty($input['desde'])) {
            $filtro_desde = " AND c.id_cliente >= " . $input['desde'];
        }

        // filtros hasta y concatena el sql correspondientes
        if (!empty($input['hasta'])) {
            $filtro_hasta = " AND c.id_cliente <= " . $input['hasta'];
        }

        // concatenar todos los filtros en el where si llega a recibir algun valor, por defecto esta where 1=1 que siempre sera true es para evitar fallo en la consulta normal
        $clientes = DB::select('SELECT c.*, ciu.descripcion as ciudad, extract(year from age(now(), c.clie_fecha_nac))as edad
            FROM clientes c 
            LEFT JOIN ciudades ciu ON c.id_ciudad = ciu.id_ciudad
            WHERE 1=1 ' . $filtro_ciudad . ' ' . $filtro_desde . ' ' . $filtro_hasta . '
            ORDER BY c.id_cliente');

        // validar que exista la variable input['exportar']
        if (isset($input['exportar']) && $input['exportar'] == 'pdf') {
            // crear pdf con loadView y utilizar la misma vista reportes rpt_clientes para convertir en pdf
            $pdf = Pdf::loadView(
                'reportes.pdf_clientes',
                compact('clientes')
            )
                ->setPaper('a4', 'landscape'); ## especificar tamaño de hoja y disposición
            # de hoja landscape=horizontal, portrait=vertical

            // retornar la descarga del archivo
            return $pdf->download("ReporteClientes.pdf");
        }

        // consulta para llenar el select de ciudad en reporte cliente
        $ciudad_select = DB::table('ciudades')->pluck('descripcion', 'id_ciudad');

        return view('reportes.rpt_clientes')->with('clientes', $clientes)->with('ciudades', $ciudad_select);
    }

    public function rpt_ventas(Request $request)
    {
        // recibir datos del formulario a partir de la url get
        $input = $request->all();

        // definir variables para filtros
        $filtro_cliente = "";
        $filtro_desde = "";
        $filtro_hasta = "";

        if (!empty($input['cliente'])) {
            // concatenar los filtros y siempre dejar un espacio en blanco al comienzo del string " "
            $filtro_cliente = " AND v.id_cliente = " . $input['cliente'];
        }

        // filtros desde y concatena el sql and
        if (!empty($input['desde'])) {
            $filtro_desde = " AND v.fecha_venta >= '" . $input['desde'] . "'";
        }

        // filtros hasta y concatena el sql correspondientes
        if (!empty($input['hasta'])) {
            $filtro_hasta = " AND v.fecha_venta <= '" . $input['hasta'] . "'";
        }

        // concatenar todos los filtros en el where si llega a recibir algun valor, por defecto esta where 1=1 que siempre sera true es para evitar fallo en la consulta normal
        $ventas = DB::select("SELECT v.*, concat(c.clie_nombre, ' ', c.clie_apellido) as cliente, u.name as usuario, s.descripcion as sucursal
            FROM ventas v 
            JOIN clientes c ON v.id_cliente = c.id_cliente
            JOIN users u ON v.user_id = u.id
            JOIN sucursales s ON v.id_sucursal = s.id_sucursal
            WHERE 1=1 " . $filtro_cliente . " " . $filtro_desde . " " . $filtro_hasta . "
            ORDER BY v.id_venta desc");

        // recuperar detalles de venta
        // definir array vacio para almacenar los detalles de venta
        $array_detalles = array();
        // validar que existan ventas
        if (count($ventas) > 0) {
            // recorrer las ventas con foreach
            foreach ($ventas as $ven) {
                // consulta para recuperar los detalles de venta
                $detalles = DB::select('SELECT dv.*, p.descripcion as producto
                    FROM detalle_ventas dv
                    LEFT JOIN productos p ON dv.id_producto = p.id_producto
                    WHERE dv.id_venta = ?', [$ven->id_venta]);
                // almacenar los detalles en el array con la llave del id_venta
                $array_detalles[$ven->id_venta] = $detalles;
            }
        }

        // validar que exista la variable input['exportar']
        if (isset($input['exportar']) && $input['exportar'] == 'pdf') {
            // crear pdf con loadView y utilizar la misma vista reportes rpt_ventas para convertir en pdf
            $pdf = Pdf::loadView(
                'reportes.pdf_ventas',
                compact('ventas', 'array_detalles')
            )
                ->setPaper('a4', 'landscape'); ## especificar tamaño de hoja y disposición
            # de hoja landscape=horizontal, portrait=vertical

            // retornar la descarga del archivo
            return $pdf->download("ReporteVentas.pdf");
        } elseif (isset($input['exportar']) && $input['exportar'] == 'excel') {
            // Validamos los parámetros para pasarlos a la clase Export
            $cliente = isset($input['cliente']) ? $input['cliente'] : null;
            $desde = isset($input['desde']) ? $input['desde'] : null;
            $hasta = isset($input['hasta']) ? $input['hasta'] : null;

            // Descargamos el Excel usando la clase del Paso 1
            return Excel::download(new VentasExport($cliente, $desde, $hasta), 'ReporteVentas.xlsx');
        }
        // consulta para llenar el select de ciudad en reporte cliente
        $clientes = DB::table('clientes')->selectRaw("concat(clie_nombre, ' ', clie_apellido) as cliente, id_cliente")->pluck('cliente', 'id_cliente');

        return view('reportes.rpt_ventas')->with('ventas', $ventas)->with('clientes', $clientes);
    }

    public function rpt_compras(Request $request)
    {
        // recibir datos del formulario a partir de la url get
        $input = $request->all();

        // definir variables para filtros
        $filtro_proveedor = "";
        $filtro_desde = "";
        $filtro_hasta = "";

        if (!empty($input['proveedor'])) {
            $filtro_proveedor = " AND c.id_proveedor = " . $input['proveedor'];
        }

        if (!empty($input['desde'])) {
            $filtro_desde = " AND c.fecha_compra >= '" . $input['desde'] . "'";
        }

        if (!empty($input['hasta'])) {
            $filtro_hasta = " AND c.fecha_compra <= '" . $input['hasta'] . "'";
        }

        // CONSULTA PRINCIPAL: USAMOS c.nro AS factura_nro Y p.descripcion AS proveedor
        // Si hay error de 'c.nro', significa que el nombre de la columna en tu BD es diferente.
        $compras = DB::select("SELECT c.*, 
                                     c.nro AS factura_nro, 
                                     p.descripcion AS proveedor, 
                                     u.name AS usuario, 
                                     s.descripcion AS sucursal
            FROM compras c 
            JOIN proveedores p ON c.id_proveedor = p.id_proveedor
            JOIN users u ON c.user_id = u.id
            JOIN sucursales s ON c.id_sucursal = s.id_sucursal 
            WHERE 1=1 " . $filtro_proveedor . " " . $filtro_desde . " " . $filtro_hasta . "
            ORDER BY c.id_compra DESC");

        // recuperar detalles de compra
        $array_detalles = array();
        if (count($compras) > 0) {
            foreach ($compras as $compra) {
                // Consulta para recuperar los detalles de compra
                $detalles = DB::select('SELECT dc.*, prod.descripcion AS producto
                    FROM detalle_compras dc
                    LEFT JOIN productos prod ON dc.id_producto = prod.id_producto
                    WHERE dc.id_compra = ?', [$compra->id_compra]);
                $array_detalles[$compra->id_compra] = $detalles;
            }
        }

        // 1. Manejo de Exportación PDF
        if (isset($input['exportar']) && $input['exportar'] == 'pdf') {
            $pdf = Pdf::loadView(
                'reportes.pdf_compras', 
                compact('compras', 'array_detalles')
            )
            ->setPaper('a4', 'landscape');
            return $pdf->download("ReporteCompras.pdf");

        // 2. Manejo de Exportación Excel
        } elseif (isset($input['exportar']) && $input['exportar'] == 'excel') {
            $proveedor = isset($input['proveedor']) ? $input['proveedor'] : null;
            $desde = isset($input['desde']) ? $input['desde'] : null;
            $hasta = isset($input['hasta']) ? $input['hasta'] : null;
            
            return Excel::download(new ComprasExport($proveedor, $desde, $hasta), 'ReporteCompras.xlsx');
        }

        // Consulta para llenar el select de proveedores en el formulario
        $proveedores = DB::table('proveedores')->selectRaw("descripcion AS nombre, id_proveedor")->pluck('nombre', 'id_proveedor');

        return view('reportes.rpt_compras')->with('compras', $compras)->with('proveedores', $proveedores)->with('array_detalles', $array_detalles);
    }
    public function rpt_proveedores(Request $request)
    {
        $input = $request->all();

        // ... (tu lógica de consulta SQL actual se queda igual) ...
        if (!empty($input['desde']) and !empty($input['hasta'])) {
            $proveedores = DB::select('SELECT * FROM proveedores WHERE id_proveedor 
        BETWEEN ' . $input['desde'] . ' AND ' . $input['hasta']);
        } else {
            $proveedores = DB::select('SELECT * FROM proveedores');
        }

        // Exportar PDF (ya lo tienes)
        if (isset($input['exportar']) && $input['exportar'] == 'pdf') {
            $pdf = Pdf::loadView('reportes.pdf_proveedores', compact('proveedores'))
                ->setPaper('a4', 'portrait');
            return $pdf->download("ReporteProveedores.pdf");
        } elseif (isset($input['exportar']) && $input['exportar'] == 'excel') {
            // Validamos los parámetros para pasarlos a la clase Export
            $desde = isset($input['desde']) ? $input['desde'] : null;
            $hasta = isset($input['hasta']) ? $input['hasta'] : null;

            // Descargamos el Excel usando la clase del Paso 1
            return Excel::download(new ProveedoresExport($desde, $hasta), 'proveedores.xlsx');
        }

        return view('reportes.rpt_proveedores')->with('proveedores', $proveedores);
    }

    public function rpt_productos(Request $request)
    {
        $input = $request->all();

        // ... (tu consulta SQL existente se queda igual) ...
        $baseQuery = 'SELECT p.id_producto, p.descripcion, p.precio, p.tipo_iva, m.descripcion AS marca 
                 FROM productos p 
                 LEFT JOIN marcas m ON p.id_marca = m.id_marca';

        if (!empty($input['desde']) and !empty($input['hasta'])) {
            $productos = DB::select($baseQuery . ' WHERE p.id_producto BETWEEN ' . $input['desde'] . ' AND ' . $input['hasta']);
        } else {
            $productos = DB::select($baseQuery);
        }

        if (isset($input['exportar']) && $input['exportar'] == 'pdf') {
            $pdf = Pdf::loadView(
                'reportes.pdf_productos',
                compact('productos')
            )->setPaper('a4', 'portrait');

            return $pdf->download("ReporteProductos.pdf");
        } elseif (isset($input['exportar']) && $input['exportar'] == 'excel') {
            $desde = $input['desde'] ?? null;
            $hasta = $input['hasta'] ?? null;

            return Excel::download(new ProductosExport($desde, $hasta), 'ReporteProductos.xlsx');
        }

        return view('reportes.rpt_productos')->with('productos', $productos);
    }
    public function rpt_sucursales(Request $request)
    {
        // recibir datos del formulario a partir de la url get
        $input = $request->all();

        // construir la consulta con join para mostrar la descripción de la ciudad
        $baseQuery = 'SELECT s.id_sucursal, s.descripcion, s.direccion, s.telefono, c.descripcion AS ciudad 
                 FROM sucursales s 
                 INNER JOIN ciudades c ON s.id_ciudad = c.id_ciudad';

        // verificar si los filtros no estan vacios para realizar el where
        if (!empty($input['desde']) and !empty($input['hasta'])) {
            $sucursales = DB::select($baseQuery . ' WHERE s.id_sucursal BETWEEN ' . $input['desde'] . ' AND ' . $input['hasta']);
        } else {
            // si no se reciben datos, se traen todos los registros
            $sucursales = DB::select($baseQuery);
        }

        if (isset($input['exportar']) && $input['exportar'] == 'pdf') {
            $pdf = Pdf::loadView(
                'reportes.pdf_sucursales',
                compact('sucursales')
            )
                ->setPaper('a4', 'portrait');

            return $pdf->download("ReporteSucursales.pdf");
        } elseif (isset($input['exportar']) && $input['exportar'] == 'excel') {
            $desde = $input['desde'] ?? null;
            $hasta = $input['hasta'] ?? null;
            return Excel::download(new SucursalesExport($desde, $hasta), 'ReporteSucursales.xlsx');
        }

        return view('reportes.rpt_sucursales')->with('sucursales', $sucursales);
    }
}
