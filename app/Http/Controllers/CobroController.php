<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use RealRashid\SweetAlert\Facades\Alert;

class CobroController extends Controller
{
    public function index(Request $request)
    {
        // recibir parametros id venta y buscar las ventas asociadas
        $id_venta = $request->get('id_venta');

        // buscar las ventas asociadas
        if (empty($id_venta)) {
            Alert::toast('Debe seleccionar una venta', 'error');
            return redirect()->route('ventas.index');
        }
        // consultar la venta
        $venta = DB::selectOne("SELECT v.*, concat(c.clie_nombre, ' ', c.clie_apellido) as cliente 
                    FROM ventas v
                    INNER JOIN clientes c ON c.id_cliente = v.id_cliente
                    WHERE id_venta = ?", [$id_venta]);

        if (empty($venta)) {
            Alert::toast('La venta no existe', 'error');
            return redirect()->route('ventas.index');
        }

        // enviar metodos de pago, listar solos los activos
        $metodos_pago = DB::table('metodo_pagos')->where('estado', true)->pluck('descripcion', 'id_metodo_pago');

        // retornar la vista de cobros que se encuentra en resources/views/ventas/cobros.blade.php
        return view('ventas.cobros')->with('ventas', $venta)->with('metodos_pago', $metodos_pago);
    }

    public function store(Request $request)
    {
        $input = $request->all();

        // validar que la venta exista
        $ventas = DB::selectOne("SELECT * FROM ventas WHERE id_venta = ?", [$input['id_venta']]);

        if (empty($ventas)) {
            Alert::toast('La venta no existe', 'error');
            return redirect()->route('ventas.index');
        }

        DB::beginTransaction();
        try {
            $total_cobros = 0; // acumulador de cobros para calculo del total
            // validar que exista la formas de pago recibidas del formulario
            if ($request->has('forma_pago')) {
                foreach ($input['forma_pago'] as $key => $metodo) {
                    // validar que la forma de pago exista
                    $metodo_pago = DB::selectOne("SELECT * FROM metodo_pagos WHERE id_metodo_pago = ?", [$metodo]);
                    if (empty($metodo_pago)) {
                        Alert::toast('La forma de pago no existe', 'error');
                        return redirect()->route('cobros.index', ['id_venta' => $input['id_venta']]);
                    }

                    $importe = str_replace('.', '', $input['importe'][$key]);
                    // guardar acumuldador de cobros
                    $total_cobros += $importe;
                    // validar que el importe sea un numero
                    if (!is_numeric($importe) || $importe <= 0) {
                        Alert::toast('El importe debe ser un numero mayor a 0', 'error');
                        return redirect()->route('cobros.index', ['id_venta' => $input['id_venta']]);
                    }

                    // registrar el cobro
                    DB::insert(
                        'INSERT INTO cobros(id_venta, user_id, id_metodo_pago, cobro_fecha, cobro_importe, cobro_estado, nro_voucher) 
                        VALUES(?, ?, ?, ?, ?, ?, ?)',
                        [
                            $ventas->id_venta,
                            auth()->user()->id,
                            $metodo,
                            Carbon::now()->format('Y-m-d'),
                            $importe,
                            'COBRADO',
                            $input['nro_voucher'][$key] ?? null
                        ]
                    );
                }

                // validar que el total de cobros sea igual al total de la venta
                if ($total_cobros != $ventas->total) {
                    Alert::toast('El total de cobros debe ser igual al total de la venta', 'error');
                    return redirect()->route('cobros.index', ['id_venta' => $input['id_venta']]);
                }
                // Actualizar el estado de la venta a PAGADO si el total de cobros es igual al total de la venta
                DB::update("UPDATE ventas SET estado = 'PAGADO' WHERE id_venta = ?", [$ventas->id_venta]);

                DB::commit();
            }
        } catch (\Exception $e) {
            // si hay un error en la transaccion, hacer rollback
            DB::rollBack();
            // registrar el error en el log
            Log::info("Error en transaccion de cobros::::::" . $e->getMessage());
            Alert::toast("Error en el proceso:" . $e->getMessage(), "error");

            return redirect()->route('cobros.index', ['id_venta' => $input['id_venta']]);
        }

        Alert::toast('Cobro guardado correctamente', 'success');
        return redirect()->route('ventas.index');
    }
}
