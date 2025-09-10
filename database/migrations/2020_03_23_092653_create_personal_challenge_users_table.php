<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePersonalChallengeUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('personal_challenge_users', function (Blueprint $table) {
            $table->bigIncrements('id')->comment("primary key of current table");
            $table->unsignedBigInteger('personal_challenge_id')->comment("refers to personal challenges table");
            $table->unsignedBigInteger('user_id')->comment("refers to users table");
            $table->boolean('joined')->default(true)->comment("default true, flag sets to true when user joins the personal challenge");
            $table->dateTime('start_date')->nullable()->comment('start date and time of challenge');
            $table->dateTime('end_date')->nullable()->comment('end date and time of challenge');
            $table->time('reminder_at')->nullable()->comment('daily reminder time of personal challenge');
            $table->boolean('completed')->default(false)->comment("default false, flag sets to true when user completes the challenge");
            $table->timestamp('created_at')->useCurrent()->comment("date and time when record is created");
            $table->timestamp('updated_at')->useCurrent()->comment("date and time when record is updated");

            $table->foreign('personal_challenge_id')
                ->references('id')
                ->on('personal_challenges')
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
        Schema::dropIfExists('personal_challenge_users');
    }
}
