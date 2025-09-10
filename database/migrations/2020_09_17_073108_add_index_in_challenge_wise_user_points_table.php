<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddIndexInChallengeWiseUserPointsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('challenge_wise_user_ponits', function (Blueprint $table) {
            $sm = Schema::getConnection()->getDoctrineSchemaManager();
            $doctrineTable = $sm->listTableDetails('challenge_wise_user_ponits');
            if (! $doctrineTable->hasIndex('user_id')) {
                $table->unsignedBigInteger('user_id')->index('user_id')->comment("refers to users table")->change();
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
        Schema::table('challenge_wise_user_ponits', function (Blueprint $table) {
            $sm = Schema::getConnection()->getDoctrineSchemaManager();
            $doctrineTable = $sm->listTableDetails('challenge_wise_user_ponits');

            if ($doctrineTable->hasIndex('user_id')) {
                $table->dropIndex('user_id');
            }
        });
    }
}
