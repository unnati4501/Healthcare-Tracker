<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEventCsatUserLogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('event_csat_user_logs', function (Blueprint $table) {
            $table->bigIncrements('id')->comment("primary key of the current table");
            $table->unsignedBigInteger('user_id')->nullable()->comment("refers to users table");
            $table->unsignedBigInteger('event_booking_log_id')->comment("refers to event booking log table  ");
            $table->enum('feedback_type', ['very_unhappy', 'unhappy', 'neutral', 'happy', 'very_happy'])->default('happy')
                ->comment("feedback type(Very Unhappy, Unhappy, Neutral, Happy, Very Happy)");
            $table->string('feedback', 1000)->nullable()->comment('feedback given by user');
            $table->timestamp('created_at')->useCurrent()->comment("date and time when record is created");
            $table->timestamp('updated_at')->useCurrent()->comment("date and time when record is updated");

            // Adding index to column
            $table->index('feedback_type');

            // Setting foreign key constraints
            $table->foreign('user_id')
                ->references('id')->on('users')
                ->onDelete('cascade');
            $table->foreign('event_booking_log_id')
                ->references('id')->on('event_booking_logs')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('event_csat_user_logs');
    }
}
