<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateContentChallengePointHistoryTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('content_challenge_point_history', function (Blueprint $table) {
            $table->bigIncrements('id')->comment("primary key of the current table");
            $table->unsignedBigInteger('challenge_id')->nullable()->comment("refers to users table");
            $table->unsignedBigInteger('user_id')->nullable()->comment("refers to users table");
            $table->string('category', 255)->comment('content category name for synced data');
            $table->string('activities', 255)->comment('Activities name for synced data');
            $table->bigInteger('points')->comment('points synced - count');
            $table->dateTime('log_date')->comment('data synced date and time');
            $table->timestamp('created_at')->useCurrent()->comment("date and time when record is created");
            $table->timestamp('updated_at')->useCurrent()->comment("date and time when record is updated");

            $table->foreign('challenge_id')
                ->references('id')->on('challenges')
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
        Schema::dropIfExists('content_challenge_point_history');
        Schema::enableForeignKeyConstraints();
    }
}
