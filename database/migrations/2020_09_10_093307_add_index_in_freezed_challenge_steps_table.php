<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddIndexInFreezedChallengeStepsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('freezed_challenge_steps', function (Blueprint $table) {
            $sm = Schema::getConnection()->getDoctrineSchemaManager();
            $doctrineTable = $sm->listTableDetails('freezed_challenge_steps');

            if (! $doctrineTable->hasIndex('user_id')) {
                $table->unsignedBigInteger('user_id')->index('user_id')->comment("refers to users table")->change();
            }

            if (! $doctrineTable->hasIndex('log_date')) {
                $table->dateTime('log_date')->index('log_date')->comment("data synced date and time")->change();
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
        Schema::table('freezed_challenge_steps', function (Blueprint $table) {
            $sm = Schema::getConnection()->getDoctrineSchemaManager();
            $doctrineTable = $sm->listTableDetails('freezed_challenge_steps');

            if ($doctrineTable->hasIndex('user_id')) {
                $table->dropIndex('user_id');
            }

            if ($doctrineTable->hasIndex('log_date')) {
                $table->dropIndex('log_date');
            }
        });
    }
}
