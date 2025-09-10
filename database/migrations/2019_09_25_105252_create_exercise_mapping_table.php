<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateExerciseMappingTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('exercise_mapping', function (Blueprint $table) {
            $table->bigIncrements('id')->comment("primary key of the current table");

            $table->unsignedBigInteger('tracker_exercise_id')->comment("refers to tracker exercises table");
            $table->unsignedBigInteger('exercise_id')->comment("refers to master exercises table");
            
            $table->timestamp('created_at')->useCurrent()->comment("date and time when record is created");
            $table->timestamp('updated_at')->useCurrent()->comment("date and time when record is updated");

            $table->foreign('tracker_exercise_id')
                ->references('id')->on('tracker_exercises')
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
        Schema::dropIfExists('exercise_mapping');
    }
}
