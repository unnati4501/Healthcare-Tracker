<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateChallengeWiseTeamPonitsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('challenge_wise_team_ponits', function (Blueprint $table) {
            $table->bigIncrements('id')->comment("primary key of current table");
            
            $table->unsignedBigInteger('challenge_id')->comment("refers to challenges table");
            $table->unsignedBigInteger('team_id')->comment("refers to teams table");

            $table->double('points')->comment('points of user in a challenge');
            $table->integer('rank')->comment('rank of user in a challenge');
            
            $table->timestamp('created_at')->useCurrent()->comment("date and time when record is created");
            $table->timestamp('updated_at')->useCurrent()->comment("date and time when record is updated");

            $table->foreign('challenge_id')
                ->references('id')->on('challenges')
                ->onDelete('cascade');
            $table->foreign('team_id')
                ->references('id')->on('teams')
                ->onDelete('cascade');
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
        Schema::dropIfExists('challenge_wise_team_ponits');
        Schema::enableForeignKeyConstraints();
    }
}
