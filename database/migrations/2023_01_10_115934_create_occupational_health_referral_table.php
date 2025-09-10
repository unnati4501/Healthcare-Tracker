<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOccupationalHealthReferralTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('occupational_health_referral', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('cronofy_schedule_id')->comment("refers to cronofy_schedule table");
            $table->unsignedBigInteger('created_by')->comment("Referes to users table and check that which WBTL has created this");
            $table->date('log_date')->comment('Logged date');
            $table->enum('is_confirmed', ['Yes', 'No'])->default('No')->comment('Client confirmation');
            $table->date('confirmation_date')->comment('Client confirmation date');
            $table->string('note', 1000)->comment('Notes added by WBTL');
            $table->enum('is_attended', ['Yes', 'No'])->default('No');
            $table->unsignedBigInteger('wellbeing_specialist_ids')->comment('wellbeing specialist ids');

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
        Schema::dropIfExists('occupational_health_referral');
    }
}
