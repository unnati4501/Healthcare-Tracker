<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCourseLessionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('course_lessions', function (Blueprint $table) {
            $table->bigIncrements('id')->comment("primary key of the current table");

            $table->unsignedBigInteger('course_id')->comment("refers to courses table");
            $table->unsignedBigInteger('course_week_id')->comment("refers to course_weeks table");
            $table->string('title', 255)->comment('lession title');
            $table->text('description')->comment('lession description');
            $table->time('duration')->nullable()->comment('total duration of course lession');
            $table->boolean('is_default')->default(false)->comment('1, if lession is course introduction');

            $table->timestamp('created_at')->useCurrent()->comment("date and time when record is created");
            $table->timestamp('updated_at')->useCurrent()->comment("date and time when record is updated");

            $table->foreign('course_id')
                ->references('id')->on('courses')
                ->onDelete('cascade');
            $table->foreign('course_week_id')
                ->references('id')->on('course_weeks')
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
        Schema::dropIfExists('course_lessions');
        Schema::enableForeignKeyConstraints();
    }
}
