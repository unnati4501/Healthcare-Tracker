<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUserPodcastLogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_podcast_logs', function (Blueprint $table) {
            $table->bigIncrements('id')->comment("primary key of the current table");

            $table->unsignedBigInteger('podcast_id')->nullable()->comment("refers to podcasts table");
            $table->unsignedBigInteger('user_id')->comment("refers to users table");
            
            $table->boolean('saved')->default(false)->comment('true, if podcast is saved by user');
            $table->boolean('liked')->default(false)->comment('true, if podcast is liked by user');
            $table->boolean('favourited')->default(false)->comment('true, if podcast is liked by user');
            $table->Integer('view_count')->default(0)->comment('Audio count when user tep no that.');

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
        Schema::dropIfExists('user_podcast_logs');
        Schema::enableForeignKeyConstraints();
    }
}
