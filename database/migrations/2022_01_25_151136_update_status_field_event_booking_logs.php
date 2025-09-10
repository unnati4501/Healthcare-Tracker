<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateStatusFieldEventBookingLogs extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('event_booking_logs', function (Blueprint $table) {
            DB::statement("ALTER TABLE `event_booking_logs` MODIFY COLUMN `status` ENUM('3','4','5','6','7','8')  NOT NULL DEFAULT '6' COMMENT ' status type 3=>Cancelled , 4=> Booked, 5=> Completed, 6=>Pending, 7=>Elapsed, 8=> Rejected'");
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
            DB::statement("ALTER TABLE `event_booking_logs` MODIFY COLUMN `status` ENUM('3','4','5')  NOT NULL DEFAULT '4' COMMENT ' status type 3=>Cancelled , 4=> Booked, 5=> Completed'");
        });
    }
}
