<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEventBookingLogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('event_booking_logs', function (Blueprint $table) {
            $table->bigIncrements('id')->comment("primary key of the current table");
            $table->unsignedBigInteger('event_id')->comment("refers to events table");
            $table->unsignedBigInteger('company_id')->comment("refers to companies table");
            $table->unsignedBigInteger('presenter_user_id')->comment("refers to users table and user is presenter of the event");
            $table->unsignedBigInteger('user_id')->nullable()->comment("refers to users table and event is booked for this user, here if user id is null means event is booked for all the users of company");
            $table->timestamp('booking_date')->useCurrent()->comment("date and time of event is booked");
            $table->time('start_time')->comment("Start time of event in HH:mm:ss format");
            $table->time('end_time')->comment("End time of event in HH:mm:ss format");
            $table->timestamp('created_at')->useCurrent()->comment("date and time when record is created");
            $table->timestamp('updated_at')->useCurrent()->comment("date and time when record is updated");

            $table->foreign('event_id')->references('id')->on('events')->onDelete('cascade');
            $table->foreign('company_id')->references('id')->on('companies')->onDelete('cascade');
            $table->foreign('presenter_user_id')->references('id')->on('users');
            $table->foreign('user_id')->references('id')->on('users');
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
        Schema::dropIfExists('event_booking_logs');
        Schema::enableForeignKeyConstraints();
    }
}
