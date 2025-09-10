<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUserCourseWeekTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_course_week', function (Blueprint $table) {
            $table->bigIncrements('id')->comment("primary key of the current table");

            $table->unsignedBigInteger('course_id')->comment("refers to courses table");
            $table->unsignedBigInteger('course_week_id')->comment("refers to course_weeks table");
            $table->unsignedBigInteger('user_id')->comment("refers to users table");
            
            $table->string('status')->nullable()->comment('status for course week');
            $table->timestamp('completed_at')->nullable()->comment("date and time when course is completed");
            
            $table->timestamp('created_at')->useCurrent()->comment("date and time when record is created");
            $table->timestamp('updated_at')->useCurrent()->comment("date and time when record is updated");

            $table->foreign('course_id')
                ->references('id')->on('courses')
                ->onDelete('cascade');
            $table->foreign('course_week_id')
                ->references('id')->on('course_weeks')
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
        Schema::dropIfExists('user_course_week');
        Schema::enableForeignKeyConstraints();
    }
}
