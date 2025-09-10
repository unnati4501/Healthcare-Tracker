<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateChallengeOngoingBadgeUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('challenge_ongoing_badge_users', function (Blueprint $table) {
            $table->bigIncrements('id')->comment("primary key of current table");
            $table->unsignedBigInteger('challenge_id')->comment("refers to challenges table");
            $table->unsignedBigInteger('badge_id')->comment("refers to badge table");
            $table->unsignedBigInteger('ongoing_badge_id')->comment("refers to ongoing challenge badge table");
            $table->unsignedBigInteger('user_id')->comment("refers to user table");
            $table->timestamp('created_at')->useCurrent()->comment("date and time when record is created");
            $table->timestamp('updated_at')->useCurrent()->comment("date and time when record is updated");

            $table->foreign('challenge_id')
                ->references('id')->on('challenges')
                ->onDelete('cascade');
            $table->foreign('user_id')
                ->references('id')->on('users')
                ->onDelete('cascade');
            $table->foreign('badge_id')
                ->references('id')->on('badges')
                ->onDelete('cascade');
            $table->foreign('ongoing_badge_id')
                ->references('id')->on('challenge_ongoing_badges')
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
        Schema::dropIfExists('challenge_ongoing_badge_users');
        Schema::enableForeignKeyConstraints();
    }
}
