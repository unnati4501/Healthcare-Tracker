<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateChallengeOngoingBadgeTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('challenge_ongoing_badges', function (Blueprint $table) {
            $table->bigIncrements('id')->comment("primary key of current table");

            $table->unsignedBigInteger('challenge_id')->comment("refers to challenges table");
            $table->unsignedBigInteger('challenge_target_id')->comment("refers to challenge_targets table");
            $table->unsignedBigInteger('badge_id')->comment("refers to badge table");
            $table->integer('target')->comment("target to assign badge on this target completed");
            $table->integer('in_days')->comment("target to assign badge on this days completed");
            $table->timestamp('created_at')->useCurrent()->comment("date and time when record is created");
            $table->timestamp('updated_at')->useCurrent()->comment("date and time when record is updated");

            $table->foreign('challenge_id')
                ->references('id')->on('challenges')
                ->onDelete('cascade');
            $table->foreign('challenge_target_id')
                ->references('id')->on('challenge_targets')
                ->onDelete('cascade');
            $table->foreign('badge_id')
                ->references('id')->on('badges')
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
        Schema::dropIfExists('challenge_ongoing_badges');
        Schema::enableForeignKeyConstraints();
    }
}
