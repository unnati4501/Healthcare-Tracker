<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUserLessionTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_lession', function (Blueprint $table) {
            $table->bigIncrements('id')->comment("primary key of the current table");

            $table->unsignedBigInteger('course_id')->comment("refers to courses table");
            $table->unsignedBigInteger('course_lession_id')->comment("refers to course_lessions table");
            $table->unsignedBigInteger('user_id')->comment("refers to users table");
            
            $table->boolean('status')->nullable()->comment('status for course lession for user which can be 1=started, 2=paused, 3=completed');
            $table->timestamp('completed_at')->comment("date and time when course is completed");
            
            $table->timestamp('created_at')->useCurrent()->comment("date and time when record is created");
            $table->timestamp('updated_at')->useCurrent()->comment("date and time when record is updated");

            $table->foreign('course_id')
                ->references('id')->on('courses')
                ->onDelete('cascade');
            $table->foreign('course_lession_id')
                ->references('id')->on('course_lessions')
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
        Schema::dropIfExists('user_lession');
        Schema::enableForeignKeyConstraints();
    }
}
