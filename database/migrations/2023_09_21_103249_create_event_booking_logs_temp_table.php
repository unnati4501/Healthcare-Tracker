<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEventBookingLogsTempTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('event_booking_logs_temp', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('event_id')->comment("refers to events table");
            $table->unsignedBigInteger('company_id')->comment("refers to companies table");
            $table->unsignedBigInteger('presenter_user_id')->comment("refers to users table and user is presenter of the event");
            $table->enum('company_type', ['Zevo', 'Company'])->default('Zevo')->comment('Check company type');
            $table->string('video_link', 255)->nullable()->comment('Video Link');
            $table->bigInteger('capacity_log')->nullable()->comment("Capacity of event");
            $table->longtext('description')->nullable()->comment('Description of event booking logs.');
            $table->text('notes')->nullable()->default(null)->comment('Email notes which will be append to event reminders email');
            $table->text('email_notes')->nullable()->default(null)->comment('Email notes which will be append to event reminders email');
            $table->longtext('cc_email')->nullable()->comment('All email of event booking logs.');
            $table->dateTime('registration_date')->nullable()->comment('time when users will be able to register');
            $table->boolean('is_complementary')->default(0)->comment('Mark booking as complementary');
            $table->boolean('add_to_story')->default(false)->comment('Event will be appear as a story if marked as true');
            $table->timestamp('created_at')->useCurrent()->comment("date and time when record is created");
            $table->timestamp('updated_at')->useCurrent()->comment("date and time when record is updated");
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('event_booking_logs_temp');
    }
}
