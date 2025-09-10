<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddLocationIdFieldInDigitalTherapySlotsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('digital_therapy_slots', function (Blueprint $table) {
            $table->unsignedBigInteger('location_id')->nullable()->after('ws_id')->comment("refers to location table but it's not foreign key ");

            $table->foreign('location_id')
                ->nullable() 
                ->references('id')
                ->on('company_locations');
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
            $table->dropForeign('digital_therapy_slots_location_id_foreign');
            $table->dropColumn(['location_id']);

        });
        Schema::enableForeignKeyConstraints();
    }
}
