<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class DropForeignKeyTeamIdInPointTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::disableForeignKeyConstraints();
        Schema::table('challenge_wise_user_ponits', function (Blueprint $table) {
            $table->dropForeign('challenge_wise_user_ponits_team_id_foreign');
        });
        Schema::table('challenge_wise_team_ponits', function (Blueprint $table) {
            $table->dropForeign('challenge_wise_team_ponits_team_id_foreign');
        });
        Schema::enableForeignKeyConstraints();
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('point', function (Blueprint $table) {
            //
        });
    }
}
