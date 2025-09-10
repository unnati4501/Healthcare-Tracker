<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateChallengeUserInspireHistoryTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('challenge_user_inspire_history', function (Blueprint $table) {

            $table->bigIncrements('id')->comment("primary key of current table");
            $table->unsignedBigInteger('challenge_id')->comment("refers to challenges table");
            $table->unsignedBigInteger('user_id')->comment("refers to users table");
            $table->unsignedBigInteger('meditation_track_id')->nullable()->comment("refers to meditation_categories table");

            $table->bigInteger('duration_listened')->comment('refers to duration of track in seconds');
            $table->double('points')->default(0);
            $table->dateTime('log_date')->comment('data synced date and time');

            $table->timestamp('created_at')->useCurrent()->comment("date and time when record is created");
            $table->timestamp('updated_at')->useCurrent()->comment("date and time when record is updated");

            $table->foreign('challenge_id')
                ->references('id')->on('challenges')
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
        Schema::dropIfExists('challenge_user_inspire_history');
        Schema::enableForeignKeyConstraints();
    }
}
