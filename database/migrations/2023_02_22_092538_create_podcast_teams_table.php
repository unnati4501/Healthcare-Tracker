<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePodcastTeamsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('podcast_team', function (Blueprint $table) {
            $table->bigIncrements('id')->comment("primary key of the current table");
            $table->unsignedBigInteger('podcast_id')->nullable()->comment("refers to podcasts table");
            $table->unsignedBigInteger('team_id')->comment("refers to teams table");

            $table->timestamp('created_at')->useCurrent()->comment("date and time when record is created");
            $table->timestamp('updated_at')->useCurrent()->comment("date and time when record is updated");

            $table->foreign('podcast_id')
                ->references('id')->on('podcasts')
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
        Schema::dropIfExists('podcast_team');
        Schema::enableForeignKeyConstraints();
    }
}
