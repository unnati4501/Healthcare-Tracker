<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateIntercompanySpTempTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('intComChUserPointListTable', function (Blueprint $table) {
            $table->bigInteger('tchID');
            $table->bigInteger('tUserId');
            $table->bigInteger('tUserTeamId');
            $table->bigInteger('tUserComId');
            $table->double('tpoint', 8, 2);
            $table->integer('trank')->default(0);
        });
        Schema::create('tempUserStepsTable', function (Blueprint $table) {
            $table->bigInteger('tchID');
            $table->bigInteger('tUserId');
            $table->string('tracker', 255);
            $table->integer('steps')->default(0);
            $table->integer('distance')->default(0);
            $table->integer('calories')->default(0);
            $table->timestamp('log_date');
        });
        Schema::create('tempUserExerciseTable', function (Blueprint $table) {
            $table->bigInteger('tchID');
            $table->bigInteger('tUserId');
            $table->bigInteger('exercise_id');
            $table->string('tracker', 255);
            $table->integer('duration')->default(0);
            $table->integer('distance')->default(0);
            $table->integer('calories')->default(0);
            $table->timestamp('start_date');
            $table->timestamp('end_date');
        });
        Schema::create('tempUserInspireTable', function (Blueprint $table) {
            $table->bigInteger('tchID');
            $table->bigInteger('tUserId');
            $table->bigInteger('meditation_track_id');
            $table->bigInteger('duration_listened')->default(0);
            $table->timestamp('log_date');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('intComChUserPointListTable');
        Schema::dropIfExists('tempUserStepsTable');
        Schema::dropIfExists('tempUserExerciseTable');
        Schema::dropIfExists('tempUserInspireTable');
    }
}
