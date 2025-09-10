<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSlotIdToEventsBookingLogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('event_booking_logs', function (Blueprint $table) {
            $table->unsignedBigInteger('slot_id')->nullable()->after('event_id')->comment("Set slot id");
            $table->foreign('slot_id')->references('id')->on('health_coach_slots')->onUpdate('CASCADE')->onDelete('CASCADE');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::disableForeignKeyConstraints();
        Schema::table('event_booking_logs', function (Blueprint $table) {
            $table->dropForeign('event_booking_logs_slot_id_foreign');
            $table->dropColumn('slot_id');
        });
        Schema::enableForeignKeyConstraints();
    }
}
