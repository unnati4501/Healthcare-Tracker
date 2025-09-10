<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddEmailNotesFieldToEventBookingLogs extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('event_booking_logs', function (Blueprint $table) {
            $table->text('email_notes')->nullable()->default(null)->comment('Email notes which will be append to event reminders email')->after('notes');
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
            $table->dropColumn('email_notes');
        });
    }
}
