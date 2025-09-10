<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RemoveForeignKeyFromDigitalTherapySlotsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('digital_therapy_slots', function (Blueprint $table) {
            $table->dropForeign('digital_therapy_slots_location_id_foreign');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::disableForeignKeyConstraints();
        Schema::table('digital_therapy_slots', function (Blueprint $table) {
            $table->foreign('location_id')
                ->nullable()
                ->references('id')->on('company_locations');
        });
        Schema::enableForeignKeyConstraints();
    }
}
