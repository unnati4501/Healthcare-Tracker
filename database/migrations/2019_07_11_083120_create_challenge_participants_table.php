<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateChallengeParticipantsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('challenge_participants', function (Blueprint $table) {
            $table->bigIncrements('id')->comment("primary key of current table");
            
            $table->unsignedBigInteger('challenge_id')->comment("refers to challenges table");
            $table->unsignedBigInteger('user_id')->comment("refers to users table");
            
            $table->string('status', 255)->default('Accepted')->comment('specifies status of invitee/joinee for the respective challenge');

            $table->timestamp('created_at')->useCurrent()->comment("date and time when record is created");
            $table->timestamp('updated_at')->useCurrent()->comment("date and time when record is updated");

            $table->foreign('challenge_id')
                ->references('id')->on('challenges')
                ->onDelete('cascade');
            $table->foreign('user_id')
                ->references('id')->on('users')
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
        Schema::dropIfExists('challenge_participants');
        Schema::enableForeignKeyConstraints();
    }
}
