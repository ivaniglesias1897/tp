<?php

namespace App\Exports;

use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class CargoExport implements FromCollection, WithHeadings
{
    /**
    * @return \Illuminate\Support\Collection
    */
    //Definicion de variables que se va a recibir
    protected $desde;
    protected $hasta;

    // Recibir los parámetros en el constructor
    public function __construct($desde, $hasta)
    {
        //Asignacion de valores a las varibales segun datos recibidos en el constructor
        $this->desde = $desde;
        $this->hasta = $hasta;
        // otras variables si es necesario
    }

    public function collection()
    {
        // Aplicamos la misma lógica que en el controlador
        if(!empty($this->desde) && !empty($this->hasta)){
            $query = DB::select('SELECT * FROM cargos WHERE id_cargo 
            BETWEEN ? AND ? ORDER BY id_cargo ASC', [$this->desde, $this->hasta]);
        } else {
            // Si no se reciben datos, se traen todos los registros
            $query = DB::select('SELECT * FROM cargos ORDER BY id_cargo ASC');
        }

        // Convertir el array a Collection
        return collect($query);
    }

    /**
     * Define los encabezados de las columnas.
     */
    public function headings(): array
    {
        return ['ID', 'Descripción'];
    }
}