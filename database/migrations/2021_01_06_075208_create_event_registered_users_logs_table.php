<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEventRegisteredUsersLogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('event_registered_users_logs', function (Blueprint $table) {
            $table->bigIncrements('id')->comment("primary key of the current table");
            $table->unsignedBigInteger('event_id')->comment("refers to events table");
            $table->unsignedBigInteger('event_booking_log_id')->comment("refers to event booking log table");
            $table->unsignedBigInteger('user_id')->nullable()->comment("refers to users table and event is booked for this user, here if user id is null means event is booked for all the users of company");
            $table->timestamp('created_at')->useCurrent()->comment("date and time when record is created");
            $table->timestamp('updated_at')->useCurrent()->comment("date and time when record is updated");
            $table->foreign('event_id')
                ->references('id')->on('events')
                ->onDelete('cascade');
            $table->foreign('event_booking_log_id')
                ->references('id')
                ->on('event_booking_logs')
                ->onUpdate('CASCADE')
                ->onDelete('CASCADE');
            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->onUpdate('CASCADE')
                ->onDelete('CASCADE');
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
        Schema::dropIfExists('event_registered_users_logs');
        Schema::enableForeignKeyConstraints();
    }
}
