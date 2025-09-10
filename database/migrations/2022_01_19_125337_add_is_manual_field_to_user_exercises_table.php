<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddIsManualFieldToUserExercisesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('user_exercise', function (Blueprint $table) {
            $table->boolean('is_manual')->after('exercise_key')->comment("default true, flag sets to false when track exercise by tracker");
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('user_exercise', function (Blueprint $table) {
            $table->dropColumn('is_manual');
        });
    }
}
