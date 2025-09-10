<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEventInviteSequenceUserLogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('event_invite_sequence_user_logs', function (Blueprint $table) {
            $table->bigIncrements('id')->comment("primary key of the current table");
            $table->unsignedBigInteger('event_booking_log_id')->comment("refers to event booking log table");
            $table->unsignedBigInteger('user_id')->nullable()->comment("refers to users table");
            $table->integer('sequence')->default(0)->comment("sequence number of email that will used for iCal");

            // setting up cardinality
            $table->foreign('event_booking_log_id')->references('id')->on('event_booking_logs')->onDelete('CASCADE');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('CASCADE');
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
        Schema::dropIfExists('event_invite_sequence_user_logs');
        Schema::enableForeignKeyConstraints();
    }
}
