<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddPointsColumnInChallengeUserExerciseHistoryTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('challenge_user_exercise_history', function (Blueprint $table) {
            $table->double('points')->default(0)->after('calories');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('challenge_user_exercise_history', function (Blueprint $table) {
            $table->dropColumn('points');
        });
    }
}
