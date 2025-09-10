<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePersonalChallengeTasksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('personal_challenge_tasks', function (Blueprint $table) {
            $table->bigIncrements('id')->comment("primary key of current table");
            $table->unsignedBigInteger('personal_challenge_id')->comment("refers to personal challenges table");
            $table->string('task_name', 255)->comment("task name of the personal challenge");
            $table->timestamp('created_at')->useCurrent()->comment("date and time when record is created");
            $table->timestamp('updated_at')->useCurrent()->comment("date and time when record is updated");

            $table->foreign('personal_challenge_id')
                ->references('id')
                ->on('personal_challenges')
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
        Schema::dropIfExists('personal_challenge_tasks');
    }
}
