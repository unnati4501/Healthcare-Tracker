<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTrackerExercisesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tracker_exercises', function (Blueprint $table) {
            $table->bigIncrements('id')->comment("primary key of the current table");

            $table->string('tracker')->comment('tracker shortname');
            $table->string('tracker_title')->comment('tracker title');
            $table->string('key', null)->nullable()->comment('key of tracker exercise');
            $table->string('name', null)->nullable()->comment('name of tracker exercise');
            
            $table->timestamp('created_at')->useCurrent()->comment("date and time when record is created");
            $table->timestamp('updated_at')->useCurrent()->comment("date and time when record is updated");
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
        Schema::dropIfExists('tracker_exercises');
        Schema::enableForeignKeyConstraints();
    }
}
