<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePersonalChallengeUserTasksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('personal_challenge_user_tasks', function (Blueprint $table) {
            $table->bigIncrements('id')->comment("primary key of current table");
            $table->unsignedBigInteger('personal_challenge_id')->comment("refers to personal challenges table");
            $table->unsignedBigInteger('user_id')->comment("refers to users table");
            $table->unsignedBigInteger('personal_challenge_tasks_id')->comment("refers to personal challenges tasks table");
            $table->dateTime('date')->nullable()->comment('daily date of task');
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

            $table->foreign('personal_challenge_tasks_id', 'personal_challenge_tasks_id')
                ->references('id')
                ->on('personal_challenge_tasks')
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
        Schema::dropIfExists('personal_challenge_user_tasks');
    }
}
