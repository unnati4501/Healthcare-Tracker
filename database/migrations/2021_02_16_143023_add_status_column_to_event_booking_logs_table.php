<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddStatusColumnToEventBookingLogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('event_booking_logs', function (Blueprint $table) {
            $table->dropColumn('is_cancelled');
            $table->enum('status', ['3', '4', '5'])->default(4)->comment('Status of event booking;3 => Cancelled, 4 => Booked, 5 => Completed')->after('end_time');

            // adding index to columns
            $table->index('status');
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
            $table->dropColumn('status');
            $table->boolean('is_cancelled')->default(false)->after('end_time')->comment('this flag will be true if event has cancelled after booking');
        });
    }
}
