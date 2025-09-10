<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateChallengeHistoryTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('challenge_history', function (Blueprint $table) {
            $table->bigIncrements('id')->comment("primary key of current table");
            
            $table->unsignedBigInteger('challenge_id')->comment("refers to challenges table");
            $table->unsignedBigInteger('creator_id')->comment("refers to users table");
            $table->unsignedBigInteger('challenge_category_id')->comment("refers to challenge_categories table");
            $table->string('timezone', 255)->comment("timezone in which challenge is created");
            $table->string('title', 255)->comment("title of the challenge");
            $table->string('description', 255)->nullable()->comment("description for the challenge");
            $table->dateTime('start_date')->comment('start date and time of challenge');
            $table->dateTime('end_date')->comment('end date and time of challenge');


            $table->timestamp('created_at')->useCurrent()->comment("date and time when record is created");
            $table->timestamp('updated_at')->useCurrent()->comment("date and time when record is updated");


            $table->foreign('challenge_id')
                ->references('id')->on('challenges')
                ->onDelete('cascade');
            $table->foreign('challenge_category_id')
                ->references('id')->on('challenge_categories')
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
        Schema::dropIfExists('challenge_history');
        Schema::enableForeignKeyConstraints();
    }
}
