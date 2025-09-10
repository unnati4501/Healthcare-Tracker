<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddRegistrationDateInEventBookingLogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('event_booking_logs', function (Blueprint $table) {
            $table->dateTime('registration_date')->nullable()->comment('time when users will be able to register')->after('end_time');
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
            $table->dropColumn('registration_date');
        });
    }
}
