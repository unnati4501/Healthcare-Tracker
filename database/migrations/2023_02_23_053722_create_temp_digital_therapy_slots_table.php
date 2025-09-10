<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTempDigitalTherapySlotsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('temp_digital_therapy_slots', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('company_id')->comment("refers to companies table");
            $table->integer('location_id')->comment("refers to company_locations table");
            $table->string('day', 3)->comment('Name of the day like sun,mon,tue,wed,thu,fri,sat');
            $table->time('start_time')->comment("start time of the slot");
            $table->time('end_time')->comment("end time of the slot");
            $table->string('ws_id')->comment("comma saperated wellbeing specialist");
            $table->timestamp('created_at')->useCurrent()->comment("date and time when record is created");
            $table->timestamp('updated_at')->useCurrent()->comment("date and time when record is updated");
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('temp_digital_therapy_slots');
    }
}
