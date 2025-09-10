<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddRegisterAllUsersFieldToEventBookingLogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('event_booking_logs', function (Blueprint $table) {
            $table->boolean('register_all_users')->default(false)->comment('Will be set as true/1 if register all users option is checked while book an event')->after('is_cancelled');
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
            $table->dropColumn('register_all_users');
        });
    }
}
