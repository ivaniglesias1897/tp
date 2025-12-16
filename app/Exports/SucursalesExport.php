<?php

namespace App\Exports;

use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class SucursalesExport implements FromCollection, WithHeadings
{
    protected $desde;
    protected $hasta;

    public function __construct($desde, $hasta)
    {
        $this->desde = $desde;
        $this->hasta = $hasta;
    }

    public function collection()
    {
        // Misma consulta base que en el controlador
        $baseQuery = 'SELECT s.id_sucursal, s.descripcion, s.direccion, s.telefono, c.descripcion AS ciudad 
                      FROM sucursales s 
                      INNER JOIN ciudades c ON s.id_ciudad = c.id_ciudad';

        if (!empty($this->desde) && !empty($this->hasta)) {
            return collect(DB::select($baseQuery . ' WHERE s.id_sucursal BETWEEN ' . $this->desde . ' AND ' . $this->hasta));
        } else {
            return collect(DB::select($baseQuery));
        }
    }

    public function headings(): array
    {
        return [
            'ID',
            'Sucursal',
            'Dirección',
            'Teléfono',
            'Ciudad'
        ];
    }
}