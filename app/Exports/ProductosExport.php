<?php

namespace App\Exports;

use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class ProductosExport implements FromCollection, WithHeadings
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
        // Usamos la misma consulta base que tienes en el controlador
        $baseQuery = 'SELECT p.id_producto, p.descripcion, p.precio, p.tipo_iva, m.descripcion AS marca 
                      FROM productos p 
                      LEFT JOIN marcas m ON p.id_marca = m.id_marca';

        if (!empty($this->desde) && !empty($this->hasta)) {
            return collect(DB::select($baseQuery . ' WHERE p.id_producto BETWEEN ' . $this->desde . ' AND ' . $this->hasta));
        } else {
            return collect(DB::select($baseQuery));
        }
    }

    public function headings(): array
    {
        return [
            'ID',
            'Descripción',
            'Precio',
            'IVA',
            'Marca'
        ];
    }
}