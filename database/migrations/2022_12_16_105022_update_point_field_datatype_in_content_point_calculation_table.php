<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdatePointFieldDatatypeInContentPointCalculationTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('content_point_calculation', function (Blueprint $table) {
            $table->float('points', 10, 0)->comment('points synced - count')->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('content_point_calculation', function (Blueprint $table) {
            $table->bigInteger('points')->comment('points synced - count')->change();
        });
    }
}
