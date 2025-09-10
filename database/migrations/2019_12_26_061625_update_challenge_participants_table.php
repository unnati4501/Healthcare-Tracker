<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateChallengeParticipantsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::disableForeignKeyConstraints();
        Schema::table('challenge_participants', function (Blueprint $table) {
            $table->unsignedBigInteger('user_id')->nullable()->comment("refers to users table")->change();
            $table->unsignedBigInteger('team_id')->after('user_id')->nullable()->comment("refers to teams table");
            $table->foreign('team_id')
                ->references('id')
                ->on('teams')
                ->onDelete('CASCADE');
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
        Schema::disableForeignKeyConstraints();
        Schema::table('challenge_participants', function (Blueprint $table) {
            $table->unsignedBigInteger('user_id')->nullable(false)->comment("refers to users table")->change();
            $table->dropForeign('challenge_participants_team_id_foreign');
            $table->dropColumn('team_id');
        });
        Schema::enableForeignKeyConstraints();
    }
}
