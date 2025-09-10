<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateContentsTeamsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('webinar_team', function (Blueprint $table) {
            $table->bigIncrements('id')->comment("primary key of the current table");
            $table->unsignedBigInteger('webinar_id')->nullable()->comment("refers to webinar table");
            $table->unsignedBigInteger('team_id')->comment("refers to teams table");
            $table->timestamp('created_at')->useCurrent()->comment("date and time when record is created");
            $table->timestamp('updated_at')->useCurrent()->comment("date and time when record is updated");

            $table->foreign('webinar_id')
                ->references('id')->on('webinar')
                ->onDelete('cascade');
            $table->foreign('team_id')
                ->references('id')->on('teams')
                ->onDelete('cascade');
        });
        Schema::create('recipe_team', function (Blueprint $table) {
            $table->bigIncrements('id')->comment("primary key of the current table");
            $table->unsignedBigInteger('recipe_id')->nullable()->comment("refers to recipe table");
            $table->unsignedBigInteger('team_id')->comment("refers to teams table");

            $table->timestamp('created_at')->useCurrent()->comment("date and time when record is created");
            $table->timestamp('updated_at')->useCurrent()->comment("date and time when record is updated");

            $table->foreign('recipe_id')
                ->references('id')->on('recipe')
                ->onDelete('cascade');
            $table->foreign('team_id')
                ->references('id')->on('teams')
                ->onDelete('cascade');
        });
        Schema::create('feed_team', function (Blueprint $table) {
            $table->bigIncrements('id')->comment("primary key of the current table");

            $table->unsignedBigInteger('feed_id')->nullable()->comment("refers to feeds table");
            $table->unsignedBigInteger('team_id')->comment("refers to teams table");

            $table->timestamp('created_at')->useCurrent()->comment("date and time when record is created");
            $table->timestamp('updated_at')->useCurrent()->comment("date and time when record is updated");

            $table->foreign('feed_id')
                ->references('id')->on('feeds')
                ->onDelete('cascade');
            $table->foreign('team_id')
                ->references('id')->on('teams')
                ->onDelete('cascade');
        });
        Schema::create('meditation_tracks_team', function (Blueprint $table) {
            $table->bigIncrements('id')->comment("primary key of the current table");
            $table->unsignedBigInteger('meditation_track_id')->nullable()->comment("refers to meditation table");
            $table->unsignedBigInteger('team_id')->comment("refers to teams table");

            $table->timestamp('created_at')->useCurrent()->comment("date and time when record is created");
            $table->timestamp('updated_at')->useCurrent()->comment("date and time when record is updated");

            $table->foreign('meditation_track_id')
                ->references('id')->on('meditation_tracks')
                ->onDelete('cascade');
            $table->foreign('team_id')
                ->references('id')->on('teams')
                ->onDelete('cascade');
        });
        Schema::create('masterclass_team', function (Blueprint $table) {
            $table->bigIncrements('id')->comment("primary key of the current table");
            $table->unsignedBigInteger('masterclass_id')->nullable()->comment("refers to courses table");
            $table->unsignedBigInteger('team_id')->comment("refers to teams table");
            $table->timestamp('created_at')->useCurrent()->comment("date and time when record is created");
            $table->timestamp('updated_at')->useCurrent()->comment("date and time when record is updated");
            $table->foreign('masterclass_id')->references('id')->on('courses')->onDelete('cascade');
            $table->foreign('team_id')->references('id')->on('teams')->onDelete('cascade');
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
        Schema::dropIfExists('webinar_team');
        Schema::dropIfExists('recipe_team');
        Schema::dropIfExists('feed_team');
        Schema::dropIfExists('meditation_tracks_team');
        Schema::dropIfExists('masterclass_team');
        Schema::enableForeignKeyConstraints();
    }
}
