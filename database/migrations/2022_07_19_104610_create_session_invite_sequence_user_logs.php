<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSessionInviteSequenceUserLogs extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('session_invite_sequence_user_logs', function (Blueprint $table) {
            $table->bigIncrements('id')->comment("primary key of the current table");
            $table->unsignedBigInteger('session_id')->comment("refers to cronofy_schedule table");
            $table->integer('user_id')->nullable()->comment("refers to users table");
            $table->integer('sequence')->default(0)->comment("sequence number of email that will used for iCal");

            // setting up cardinality
            $table->foreign('session_id')->references('id')->on('cronofy_schedule')->onDelete('CASCADE');
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
        Schema::dropIfExists('session_invite_sequence_user_logs');
        Schema::enableForeignKeyConstraints();
    }
}
