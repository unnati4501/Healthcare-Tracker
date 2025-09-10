<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddWsIdsFieldInDigitalTherapySlotsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('digital_therapy_slots', function (Blueprint $table) {
            $table->string('ws_id')->nullable()->comment('Assign ws ids for digital therapy slots')->after('end_time');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('digital_therapy_slots', function (Blueprint $table) {
            $table->dropColumn('ws_id');
        });
    }
}
