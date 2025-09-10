<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddTeamIdInChallengeWiseUserLogTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('challenge_wise_user_log', function (Blueprint $table) {
            $table->unsignedBigInteger('user_id')->nullable()->comment("refers to users table")->change();
            $table->unsignedBigInteger('team_id')->after('user_id')->nullable()->comment("refers to teams table");
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
        Schema::table('challenge_wise_user_log', function (Blueprint $table) {
            $table->unsignedBigInteger('user_id')->nullable(false)->comment("refers to users table")->change();
            $table->dropColumn('team_id');
        });
        Schema::enableForeignKeyConstraints();
    }
}
