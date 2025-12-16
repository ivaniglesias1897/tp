<?php

namespace App\Exports;

use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class ProveedoresExport implements FromCollection, WithHeadings
{
    protected $desde;
    protected $hasta;

    // Recibimos los filtros en el constructor
    public function __construct($desde, $hasta)
    {
        $this->desde = $desde;
        $this->hasta = $hasta;
    }

    public function collection()
    {
        // Usamos la misma lógica SQL que tienes en el controlador
        if (!empty($this->desde) && !empty($this->hasta)) {
            return collect(DB::select('SELECT * FROM proveedores WHERE id_proveedor BETWEEN ' . $this->desde . ' AND ' . $this->hasta));
        } else {
            return collect(DB::select('SELECT * FROM proveedores'));
        }
    }

    // Encabezados de las columnas en el Excel
    public function headings(): array
    {
        return [
            'ID',
            'Descripción / Razón Social',
            'Dirección',
            'Teléfono',
            // Agrega aquí cualquier otro campo que tenga tu tabla proveedores
        ];
    }
}