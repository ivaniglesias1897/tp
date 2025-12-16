<?php

namespace App\Exports;

use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class VentasExport implements FromCollection, WithHeadings
{
    protected $cliente;
    protected $desde;
    protected $hasta;

    public function __construct($cliente, $desde, $hasta)
    {
        $this->cliente = $cliente;
        $this->desde = $desde;
        $this->hasta = $hasta;
    }

    public function collection()
    {
        // 1. Construir filtros
        $filtro_cliente = "";
        $filtro_desde = "";
        $filtro_hasta = "";

        if (!empty($this->cliente)) {
            $filtro_cliente = " AND v.id_cliente = " . $this->cliente;
        }
        if (!empty($this->desde)) {
            $filtro_desde = " AND v.fecha_venta >= '" . $this->desde . "'";
        }
        if (!empty($this->hasta)) {
            $filtro_hasta = " AND v.fecha_venta <= '" . $this->hasta . "'";
        }

        // 2. Ejecutar consulta (Igual al controlador)
        return collect(DB::select("SELECT v.id_venta, 
                                          concat(c.clie_nombre, ' ', c.clie_apellido) as cliente, 
                                          v.fecha_venta, 
                                          v.condicion_venta, 
                                          v.total, 
                                          v.estado, 
                                          v.factura_nro, 
                                          s.descripcion as sucursal, 
                                          u.name as usuario
                                   FROM ventas v 
                                   JOIN clientes c ON v.id_cliente = c.id_cliente
                                   JOIN users u ON v.user_id = u.id
                                   JOIN sucursales s ON v.id_sucursal = s.id_sucursal
                                   WHERE 1=1 " . $filtro_cliente . " " . $filtro_desde . " " . $filtro_hasta . "
                                   ORDER BY v.id_venta desc"));
    }

    public function headings(): array
    {
        return [
            'ID Venta',
            'Cliente',
            'Fecha',
            'Condición',
            'Total',
            'Estado',
            'Nro Factura',
            'Sucursal',
            'Usuario',
        ];
    }
}