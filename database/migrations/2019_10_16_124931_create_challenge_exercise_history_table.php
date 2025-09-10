<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateChallengeExerciseHistoryTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('challenge_exercise_history', function (Blueprint $table) {
            $table->bigIncrements('id')->comment("primary key of current table");
            $table->unsignedBigInteger('challenge_id')->comment("refers to challenges table");
            $table->unsignedBigInteger('user_id')->comment("refers to users table");
            $table->unsignedBigInteger('exercise_id')->comment("refers to exercises table");

            $table->string('tracker')->comment('tracker shortname for synced data');
            $table->bigInteger('duration')->comment('duration synced - seconds');
            $table->bigInteger('distance')->comment('distance synced - meter');
            $table->bigInteger('calories')->comment('calories synced - kcal');
            $table->dateTime('start_date')->comment('data synced - start date and time');
            $table->dateTime('end_date')->comment('data synced - end date and time');

            $table->timestamp('created_at')->useCurrent()->comment("date and time when record is created");
            $table->timestamp('updated_at')->useCurrent()->comment("date and time when record is updated");

            $table->foreign('challenge_id')
                ->references('id')->on('challenges')
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
        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('challenge_exercise_history');
        Schema::enableForeignKeyConstraints();
    }
}
