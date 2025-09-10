<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateChallengeExtraPoints extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('challenge_extra_points', function (Blueprint $table) {
            $table->bigIncrements('id')->comment('Primary key of the current table');

            $table->unsignedBigInteger('challenge_id')->comment("refers to challenges table");
            $table->unsignedBigInteger('challenge_target_id')->comment("refers to challenge_targets table");
            $table->unsignedBigInteger('user_id')->comment("refers to users table");
            $table->dateTime('logged_at')->nullable();
            $table->double('points')->default(0)->comment('extra points of target for the logged_at');

            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent();


            $table->foreign('challenge_id')
                ->references('id')->on('challenges')
                ->onDelete('cascade');

            $table->foreign('challenge_target_id')
                ->references('id')->on('challenge_targets')
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
        Schema::dropIfExists('challenge_extra_points');
        Schema::enableForeignKeyConstraints();
    }
}
