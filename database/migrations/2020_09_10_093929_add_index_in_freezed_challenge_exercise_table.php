<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddIndexInFreezedChallengeExerciseTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('freezed_challenge_exercise', function (Blueprint $table) {
            $sm = Schema::getConnection()->getDoctrineSchemaManager();
            $doctrineTable = $sm->listTableDetails('freezed_challenge_exercise');

            if (! $doctrineTable->hasIndex('user_id')) {
                $table->unsignedBigInteger('user_id')->index('user_id')->comment("refers to users table")->change();
            }

            if (! $doctrineTable->hasIndex('exercise_id')) {
                $table->unsignedBigInteger('exercise_id')->index('exercise_id')->comment("refers to exercises table")->change();
            }

            if (! $doctrineTable->hasIndex('start_date')) {
                $table->dateTime('start_date')->index('start_date')->comment("data synced - start date and time")->change();
            }

            if (! $doctrineTable->hasIndex('end_date')) {
                $table->dateTime('end_date')->index('end_date')->comment("data synced - end date and time")->change();
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('freezed_challenge_exercise', function (Blueprint $table) {
            $sm = Schema::getConnection()->getDoctrineSchemaManager();
            $doctrineTable = $sm->listTableDetails('freezed_challenge_exercise');

            if ($doctrineTable->hasIndex('user_id')) {
                $table->dropIndex('user_id');
            }

            if ($doctrineTable->hasIndex('exercise_id')) {
                $table->dropIndex('exercise_id');
            }

            if ($doctrineTable->hasIndex('start_date')) {
                $table->dropIndex('start_date');
            }

            if ($doctrineTable->hasIndex('end_date')) {
                $table->dropIndex('end_date');
            }
        });
    }
}
