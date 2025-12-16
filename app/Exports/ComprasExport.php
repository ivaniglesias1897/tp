<?php

namespace App\Exports;

use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class ComprasExport implements FromCollection, WithHeadings
{
    // Variables para almacenar los filtros
    protected $proveedor;
    protected $desde;
    protected $hasta;

    public function __construct($proveedor, $desde, $hasta)
    {
        $this->proveedor = $proveedor;
        $this->desde = $desde;
        $this->hasta = $hasta;
    }

    public function collection()
    {
        // 1. Construir filtros
        $filtro_proveedor = "";
        $filtro_desde = "";
        $filtro_hasta = "";

        if (!empty($this->proveedor)) {
            $filtro_proveedor = " AND c.id_proveedor = " . $this->proveedor;
        }
        if (!empty($this->desde)) {
            $filtro_desde = " AND c.fecha_compra >= '" . $this->desde . "'";
        }
        if (!empty($this->hasta)) {
            $filtro_hasta = " AND c.fecha_compra <= '" . $this->hasta . "'";
        }

        // 2. Consulta SQL: USAMOS c.nro Y p.descripcion
        return collect(DB::select("SELECT c.id_compra, 
                                          p.descripcion AS proveedor,
                                          c.fecha_compra,
                                          c.condicion_compra,
                                          c.total,
                                          c.estado,
                                          c.nro AS factura_nro, 
                                          s.descripcion AS sucursal,
                                          u.name AS usuario
                                  FROM compras c 
                                  JOIN proveedores p ON c.id_proveedor = p.id_proveedor
                                  JOIN users u ON c.user_id = u.id
                                  JOIN sucursales s ON c.id_sucursal = s.id_sucursal
                                  WHERE 1=1 " . $filtro_proveedor . " " . $filtro_desde . " " . $filtro_hasta . "
                                  ORDER BY c.id_compra DESC"));
    }

    public function headings(): array
    {
        return [
            'ID Compra',
            'Proveedor',
            'Fecha',
            'Condición',
            'Total',
            'Estado',
            'Nro Factura', // Coincide con el alias factura_nro
            'Sucursal',
            'Usuario',
        ];
    }
}