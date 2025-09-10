<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUserExerciseTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_exercise', function (Blueprint $table) {
            $table->bigIncrements('id')->comment("primary key of the current table");

            $table->unsignedBigInteger('user_id')->comment("refers to users table");
            $table->unsignedBigInteger('exercise_id')->comment("refers to exercises table");
            
            $table->string('tracker')->comment('tracker shortname for synced data');
            $table->string('exercise_key')->comment('key of tracker for synced data');
            $table->bigInteger('duration')->comment('duration synced - seconds');
            $table->bigInteger('distance')->comment('distance synced - meter');
            $table->bigInteger('calories')->comment('calories synced - kcal');
            $table->dateTime('start_date')->comment('data synced - start date and time');
            $table->dateTime('end_date')->comment('data synced - end date and time');
            $table->string('route_url')->nullable()->comment('image url of route covered by activity');
            
            $table->timestamp('created_at')->useCurrent()->comment("date and time when record is created");
            $table->timestamp('updated_at')->useCurrent()->comment("date and time when record is updated");

            $table->foreign('user_id')
                ->references('id')->on('users')
                ->onDelete('cascade');
            $table->foreign('exercise_id')
                ->references('id')->on('exercises')
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
        Schema::dropIfExists('user_exercise');
        Schema::enableForeignKeyConstraints();
    }
}
