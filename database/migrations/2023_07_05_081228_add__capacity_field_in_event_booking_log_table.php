<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCapacityFieldInEventBookingLogTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('event_booking_logs', function (Blueprint $table) {
            $table->bigInteger('capacity')->nullable()->comment("Capacity of event")->after('video_link');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('event_booking_logs', function (Blueprint $table) {
            $table->dropColumn('capacity');
        });
    }
}
