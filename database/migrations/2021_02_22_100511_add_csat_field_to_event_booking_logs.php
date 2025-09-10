<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCsatFieldToEventBookingLogs extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('event_booking_logs', function (Blueprint $table) {
            $table->boolean('is_csat')->default(false)->comment('Based on CSAT(Feedback) flag survey for the event will be triggered')->after('register_all_users');
            $table->timestamp('csat_at')->nullable()->comment('Date and Time when event CSAT(Feedback) notification has been sent to registered users after 12 hours of event get complete')->after('today_reminder_at');
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
            $table->dropColumn('is_csat');
            $table->dropColumn('csat_at');
        });
    }
}
