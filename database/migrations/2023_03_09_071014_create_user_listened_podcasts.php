<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUserListenedPodcasts extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_listened_podcasts', function (Blueprint $table) {
            $table->bigIncrements('id')->comment("primary key of the current table");

            $table->unsignedBigInteger('podcast_id')->nullable()->comment("refers to podcasts table");
            $table->unsignedBigInteger('user_id')->comment("refers to users table");
            
            $table->bigInteger('duration_listened')->comment('refers to duration of podcast in seconds');
            
            $table->timestamp('created_at')->useCurrent()->comment("date and time when record is created");
            $table->timestamp('updated_at')->useCurrent()->comment("date and time when record is updated");

            $table->foreign('podcast_id')
                ->references('id')->on('podcasts')
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
        Schema::dropIfExists('user_listened_podcasts');
        Schema::enableForeignKeyConstraints();
    }
}
