<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class AddCheckConstraintToStocksTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        // Esta sentencia SQL le dice a la base de datos:
        // "Añade una regla llamada 'chk_stock_no_negativo' que obligue a que 
        // la columna 'cantidad' siempre sea mayor o igual a 0".
        DB::statement('ALTER TABLE stocks ADD CONSTRAINT chk_stock_no_negativo CHECK (cantidad >= 0)');
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        // Si revertimos la migración, borramos la regla.
        DB::statement('ALTER TABLE stocks DROP CONSTRAINT chk_stock_no_negativo');
    }
}