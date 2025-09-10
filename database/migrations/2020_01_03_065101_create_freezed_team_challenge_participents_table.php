<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateFreezedTeamChallengeParticipentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('freezed_team_challenge_participents', function (Blueprint $table) {
            $table->bigIncrements('id')->comment("primary key of current table");
            $table->unsignedBigInteger('challenge_id')->comment("refers to challenges table");
            $table->unsignedBigInteger('user_id')->nullable()->comment("refers to users table");
            $table->unsignedBigInteger('team_id')->nullable()->comment("refers to team table");
            $table->string('participant_name', 255)->nullable()->comment("participant name from user table");
            $table->string('timezone', 255)->nullable()->comment("user timezone");
            $table->enum('challenge_type', ['individual', 'team', 'company_goal'])->default('individual');
            
            $table->timestamp('created_at')->useCurrent()->comment("date and time when record is created");
            $table->timestamp('updated_at')->useCurrent()->comment("date and time when record is updated");

            $table->foreign('challenge_id')
                ->references('id')->on('challenges')
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
        Schema::dropIfExists('freezed_team_challenge_participents');
        Schema::enableForeignKeyConstraints();
    }
}
