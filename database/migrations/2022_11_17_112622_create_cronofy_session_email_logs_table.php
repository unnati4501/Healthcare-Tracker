<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCronofySessionEmailLogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cronofy_session_email_logs', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('cronofy_schedule_id')->comment("refers to cronofy_schedule table");
            $table->string('reason')->comment('reason saved when WS send cancel request');
            $table->text('email_message')->nullable()->comment('email body when WS send cancel request');

            $table->foreign('cronofy_schedule_id')
                ->references('id')->on('cronofy_schedule')
                ->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('cronofy_session_email_logs');
    }
}
