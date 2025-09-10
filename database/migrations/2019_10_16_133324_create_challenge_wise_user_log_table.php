<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateChallengeWiseUserLogTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('challenge_wise_user_log', function (Blueprint $table) {
            $table->bigIncrements('id')->comment("primary key of current table");
            $table->unsignedBigInteger('challenge_id')->comment("refers to challenges table");
            $table->unsignedBigInteger('user_id')->comment("refers to users table");

            $table->boolean('is_disqualified', false)->default(false)->comment('flag to identify that user is disqualified from the respective challenge or not');

            $table->dateTime('disqualified_at')->nullable()->comment('refers to date on which user disqualified from challenge');

            $table->boolean('is_winner', false)->default(false)->comment('flag to identify that user won the challenge or not');

            $table->dateTime('won_at')->nullable()->comment('refers to challenge winning time for the user');
            
            $table->dateTime('finished_at')->nullable()->comment('refers to challenge finished time for the user');
            
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
        Schema::dropIfExists('challenge_wise_user_log');
        Schema::enableForeignKeyConstraints();
    }
}
