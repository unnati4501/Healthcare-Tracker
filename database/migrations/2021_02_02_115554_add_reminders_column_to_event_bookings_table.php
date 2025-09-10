<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddRemindersColumnToEventBookingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('event_booking_logs', function (Blueprint $table) {
            $table->dropColumn('reminder_at');
            $table->timestamp('tomorrow_reminder_at')->nullable()->comment('Date and Time when event start reminder(reminder will be send before 12 hours of event start time) has been sent')->after('meta');
            $table->timestamp('today_reminder_at')->nullable()->comment('Date and Time when event start reminder(reminder will be send before 1 hour of event start time) has been sent')->after('tomorrow_reminder_at');
            $table->index('tomorrow_reminder_at');
            $table->index('today_reminder_at');
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
            $table->dropColumn('tomorrow_reminder_at');
            $table->dropColumn('today_reminder_at');
            $table->timestamp('reminder_at')->nullable()->comment('Date and Time when event start reminder has been sent')->after('meta');
            $table->index('reminder_at');
        });
    }
}
