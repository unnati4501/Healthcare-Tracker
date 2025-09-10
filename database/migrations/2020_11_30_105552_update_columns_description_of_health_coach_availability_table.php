<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateColumnsDescriptionOfHealthCoachAvailabilityTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('health_coach_availability', function (Blueprint $table) {
            $table->datetime('from_date')->comment("from date and time of availability")->change();
            $table->datetime('to_date')->comment("to date and time of availability")->change();
        });
    }
}
