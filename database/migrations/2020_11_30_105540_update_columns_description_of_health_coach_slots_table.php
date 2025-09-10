<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateColumnsDescriptionOfHealthCoachSlotsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('health_coach_slots', function (Blueprint $table) {
            $table->time('start_time')->comment("start time of the slot")->change();
            $table->time('end_time')->comment("end time of the slot")->change();
        });
    }
}
