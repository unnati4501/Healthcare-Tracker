<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCronofySessionsAttachmentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cronofy_sessions_attachments', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('cronofy_schedule_id')->nullable()->comment("refers to cronofy_schedule table");
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
        Schema::dropIfExists('cronofy_sessions_attachments');
    }
}
