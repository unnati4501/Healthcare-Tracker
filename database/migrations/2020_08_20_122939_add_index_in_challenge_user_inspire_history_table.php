<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddIndexInChallengeUserInspireHistoryTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('challenge_user_inspire_history', function (Blueprint $table) {
            $sm = Schema::getConnection()->getDoctrineSchemaManager();
            $doctrineTable = $sm->listTableDetails('challenge_user_inspire_history');

            if (! $doctrineTable->hasIndex('user_id')) {
                $table->unsignedBigInteger('user_id')->index('user_id')->comment("refers to users table")->change();
            }

            if (! $doctrineTable->hasIndex('meditation_track_id')) {
                $table->unsignedBigInteger('meditation_track_id')->index('meditation_track_id')->nullable()->comment("refers to meditation_categories table")->change();
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
        Schema::table('challenge_user_inspire_history', function (Blueprint $table) {
            $sm = Schema::getConnection()->getDoctrineSchemaManager();
            $doctrineTable = $sm->listTableDetails('challenge_user_inspire_history');

            if ($doctrineTable->hasIndex('user_id')) {
                $table->dropIndex('user_id');
            }
            if ($doctrineTable->hasIndex('meditation_track_id')) {
                $table->dropIndex('meditation_track_id');
            }
        });
    }
}
