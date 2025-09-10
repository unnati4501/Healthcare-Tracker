<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUserMeditationTrackLogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_meditation_track_logs', function (Blueprint $table) {
            $table->bigIncrements('id')->comment("primary key of the current table");

            $table->unsignedBigInteger('meditation_track_id')->nullable()->comment("refers to meditation tracks table");
            $table->unsignedBigInteger('user_id')->comment("refers to users table");
            
            $table->boolean('saved')->default(false)->comment('true, if meditation track is saved by user');
            $table->boolean('liked')->default(false)->comment('true, if meditation track is liked by user');
            $table->boolean('favourited')->default(false)->comment('true, if meditation track is liked by user');
            
            $table->timestamp('created_at')->useCurrent()->comment("date and time when record is created");
            $table->timestamp('updated_at')->useCurrent()->comment("date and time when record is updated");

            $table->foreign('meditation_track_id')
                ->references('id')->on('meditation_tracks')
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
        Schema::dropIfExists('user_meditation_track_logs');
        Schema::enableForeignKeyConstraints();
    }
}
