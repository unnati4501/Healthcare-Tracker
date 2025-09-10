<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCoursesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('courses', function (Blueprint $table) {
            $table->bigIncrements('id')->comment("primary key of the current table");

            $table->unsignedBigInteger('creator_id')->comment("refers to users table- creator of the course");
            $table->unsignedBigInteger('category_id')->comment("refers to categories table");
            
            $table->string('title', 255)->comment('course title');
            $table->text('benefits')->nullable()->comment('course benefits');
            $table->text('instructions')->nullable()->comment('course instructions');
            $table->string('tag', 255)->comment('course tag: move/nourish/inspire');
            $table->string('expertise_level', 255)->comment('course expertise level');
            $table->string('deep_link_uri')->comment('represents the deep link which redirects users to the course view on app');
            $table->boolean('is_premium')->default(false)->comment('By deafult all courses will be un-locked but super admin can change it to locked as a premium content');
            
            $table->timestamp('created_at')->useCurrent()->comment("date and time when record is created");
            $table->timestamp('updated_at')->useCurrent()->comment("date and time when record is updated");

            $table->foreign('creator_id')
                ->references('id')->on('users')
                ->onDelete('cascade');
            $table->foreign('category_id')
                ->references('id')->on('categories')
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
        Schema::dropIfExists('courses');
        Schema::enableForeignKeyConstraints();
    }
}
