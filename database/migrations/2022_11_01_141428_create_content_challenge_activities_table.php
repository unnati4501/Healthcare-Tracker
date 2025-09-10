<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateContentChallengeActivitiesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('content_challenge_activities', function (Blueprint $table) {
            $table->bigIncrements('id')->comment("primary key of the current table");
            $table->string('activity')->comment("Activity name of table");
            $table->unsignedBigInteger('category_id')->comment("refers to content_challenge table");
            $table->integer('daily_limit')->default(0)->comment("Daily limit of the activity");
            $table->integer('points_per_action')->default(0)->comment("Points per action performed");
            $table->timestamp('created_at')->useCurrent()->comment("date and time when record is created");
            $table->timestamp('updated_at')->useCurrent()->comment("date and time when record is updated");

            $table->foreign('category_id')
                ->references('id')->on('content_challenge')
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
        Schema::dropIfExists('content_challenge_activities');
    }
}
