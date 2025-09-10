<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddEmailNotesSentAtFieldsToEventBookingLogs extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('event_booking_logs', function (Blueprint $table) {
            $table->timestamp('tomorrow_email_note_at')->nullable()->comment("Date and Time when 'email note' email will be sent before 24 hours")->after('today_reminder_at');
            $table->timestamp('today_email_note_at')->nullable()->comment("Date and Time when 'email note' email will be sent before 12 hours")->after('tomorrow_email_note_at');
            $table->index('tomorrow_email_note_at');
            $table->index('today_email_note_at');
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
            $table->dropColumn('tomorrow_email_note_at');
            $table->dropColumn('today_email_note_at');
        });
    }
}
