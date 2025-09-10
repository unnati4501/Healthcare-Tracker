<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RemoveSomeTempTableForChallengeSp extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::dropIfExists('intComChUserPointListTable');
        Schema::dropIfExists('tempUserStepsTable');
        Schema::dropIfExists('tempUserExerciseTable');
        Schema::dropIfExists('tempUserInspireTable');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
