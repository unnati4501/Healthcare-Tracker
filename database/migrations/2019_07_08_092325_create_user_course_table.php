<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUserCourseTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_course', function (Blueprint $table) {
            $table->bigIncrements('id')->comment("primary key of the current table");

            $table->unsignedBigInteger('course_id')->comment("refers to courses table");
            $table->unsignedBigInteger('user_id')->comment("refers to users table");
            
            $table->boolean('saved')->default(false)->comment('true, if meditation track is saved by user');
            $table->boolean('liked')->default(false)->comment('true, if meditation track is liked by user');
            $table->integer('ratings')->default(0)->comment('course ratings given by user');
            $table->string('review', 255)->nullable()->comment('course review given by user');
            
            $table->timestamp('created_at')->useCurrent()->comment("date and time when record is created");
            $table->timestamp('updated_at')->useCurrent()->comment("date and time when record is updated");

            $table->foreign('course_id')
                ->references('id')->on('courses')
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
        Schema::dropIfExists('user_course');
        Schema::enableForeignKeyConstraints();
    }
}
