<?php

namespace App\Exports;

use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class ClienteExport implements FromCollection, WithHeadings
{
    /**
     * @return \Illuminate\Support\Collection
     */
    //Definicion de variables que se va a recibir
    protected $desde;
    protected $hasta;
    protected $ciudad;
    public function __construct($desde, $hasta, $ciudad)
    {
        //Asignacion de valores a las varibales segun datos recibidos en el constructor
        $this->desde = $desde;
        $this->hasta = $hasta;
        $this->ciudad = $ciudad;
    }
    public function collection()
    {
        // definir variables para filtros
        $filtro_ciudad = "";
        $filtro_desde = "";
        $filtro_hasta = "";

        if (!empty($this->ciudad)) {
            // concatenar los filtros y siempre dejar un espacio en blanco al comienzo del string " "
            $filtro_ciudad = " AND c.id_ciudad = " . $this->ciudad;
        }

        // filtros desde y concatena el sql and
        if (!empty($this->desde)) {
            $filtro_desde = " AND c.id_cliente >= " . $this->desde;
        }

        // filtros hasta y concatena el sql correspondientes
        if (!empty($this->hasta)) {
            $filtro_hasta = " AND c.id_cliente <= " . $this->hasta;
        }

        // concatenar todos los filtros en el where si llega a recibir algun valor, por defecto esta where 1=1 que siempre sera true es para evitar fallo en la consulta normal
        $clientes = DB::select("SELECT
            c.id_cliente,
            concat(c.clie_nombre, ' ', c.clie_apellido) as cliente,
            c.clie_ci,
            c.clie_telefono,
            c.clie_fecha_nac,
            c.clie_direccion,
            ciu.descripcion as ciudad,
            extract(year from age(now(), c.clie_fecha_nac)) as edad
            FROM clientes c
            LEFT JOIN ciudades ciu ON c.id_ciudad = ciu.id_ciudad
            WHERE 1=1 $filtro_ciudad $filtro_desde $filtro_hasta
            ORDER BY c.id_cliente");

        // Asegurar que cada fila sea un array indexado en el mismo orden que los headings
        $clientes = array_map(function($r) {
            return [
                $r->id_cliente,
                $r->cliente,
                $r->clie_ci,
                $r->clie_telefono,
                $r->clie_fecha_nac,
                $r->clie_direccion,
                $r->ciudad,
                $r->edad,
            ];
        }, $clientes);

        // Convertir el array a Collection
        return collect($clientes);
    }

    /**
     * Define los encabezados de las columnas.
     */
    public function headings(): array
    {
        return ['ID', 'Cliente', 'CI', 'Teléfono', 'Fecha Nacimiento', 'Dirección', 'Ciudad', 'Edad'];
    }
}
