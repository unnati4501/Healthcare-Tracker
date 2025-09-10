<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UpdateFreezedChallengeParticipantsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('freezed_challenge_participents', function (Blueprint $table) {
            $table->unsignedBigInteger('user_id')->nullable()->comment("refers to users table")->change();
            $table->unsignedBigInteger('team_id')->after('user_id')->nullable()->comment("refers to teams table");
            $table->string('participant_name', 255)->nullable()->comment("participant name from user table")->change();
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
        Schema::table('freezed_challenge_participents', function (Blueprint $table) {
            $table->unsignedBigInteger('user_id')->nullable(false)->comment("refers to users table")->change();
            $table->string('participant_name', 255)->nullable(false)->comment("participant name from user table")->change();
            $table->dropColumn('team_id');
        });
        Schema::enableForeignKeyConstraints();
    }
}
