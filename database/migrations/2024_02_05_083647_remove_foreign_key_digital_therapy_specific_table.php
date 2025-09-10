<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RemoveForeignKeyDigitalTherapySpecificTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('digital_therapy_specific', function (Blueprint $table) {
            $table->dropForeign('digital_therapy_specific_location_id_foreign');
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
        Schema::table('digital_therapy_specific', function (Blueprint $table) {
            $table->foreign('location_id')
                ->nullable()
                ->references('id')->on('company_locations');
        });
        Schema::enableForeignKeyConstraints();
    }
}
