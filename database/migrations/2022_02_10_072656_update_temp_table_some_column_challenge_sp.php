<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateTempTableSomeColumnChallengeSp extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('indUserPointListTable', function (Blueprint $table) {
            $table->bigInteger('tUserId')->nullable()->change();
            $table->bigInteger('tUserTeamId')->nullable()->change();
        });
        Schema::table('teamChUserPointListTable', function (Blueprint $table) {
            $table->bigInteger('tUserId')->nullable()->change();
            $table->bigInteger('tUserTeamId')->nullable()->change();
        });
        Schema::table('companyChUserPointListTable', function (Blueprint $table) {
            $table->bigInteger('tUserId')->nullable()->change();
            $table->bigInteger('tUserTeamId')->nullable()->change();
        });
        Schema::table('intComChUserPointListTable', function (Blueprint $table) {
            $table->bigInteger('tUserId')->nullable()->change();
            $table->bigInteger('tUserTeamId')->nullable()->change();
            $table->bigInteger('tUserComId')->nullable()->change();
        });
        Schema::table('tempUserStepsTable', function (Blueprint $table) {
            $table->bigInteger('tchID')->nullable()->change();
            $table->bigInteger('tUserId')->nullable()->change();
        });
        Schema::table('tempUserExerciseTable', function (Blueprint $table) {
            $table->bigInteger('tUserId')->nullable()->change();
            $table->bigInteger('exercise_id')->nullable()->change();
        });
        Schema::table('tempUserInspireTable', function (Blueprint $table) {
            $table->bigInteger('tUserId')->nullable()->change();
            $table->bigInteger('meditation_track_id')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('indUserPointListTable', function (Blueprint $table) {
            $table->bigInteger('tUserId')->change();
            $table->bigInteger('tUserTeamId')->change();
        });
        Schema::table('teamChUserPointListTable', function (Blueprint $table) {
            $table->bigInteger('tUserId')->change();
            $table->bigInteger('tUserTeamId')->change();
        });
        Schema::table('companyChUserPointListTable', function (Blueprint $table) {
            $table->bigInteger('tUserId')->change();
            $table->bigInteger('tUserTeamId')->change();
        });
        Schema::table('intComChUserPointListTable', function (Blueprint $table) {
            $table->bigInteger('tUserId')->change();
            $table->bigInteger('tUserTeamId')->change();
            $table->bigInteger('tUserComId')->change();
        });
        Schema::table('tempUserStepsTable', function (Blueprint $table) {
            $table->bigInteger('tchID')->change();
            $table->bigInteger('tUserId')->change();
        });
        Schema::table('tempUserExerciseTable', function (Blueprint $table) {
            $table->bigInteger('tUserId')->change();
            $table->bigInteger('exercise_id')->change();
        });
        Schema::table('tempUserInspireTable', function (Blueprint $table) {
            $table->bigInteger('tUserId')->change();
            $table->bigInteger('meditation_track_id')->change();
        });
    }
}
