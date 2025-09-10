<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateShortsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('shorts', function (Blueprint $table) {
            $table->bigIncrements('id')->comment("primary key of the current table");
            $table->unsignedBigInteger('category_id')->default(9)->comment("refers to categories table");
            $table->unsignedBigInteger('sub_category_id')->nullable()->comment("refers to sub_categories table");
            $table->unsignedBigInteger('tag_id')->nullable()->comment("refers to category_tags table");
            $table->unsignedBigInteger('author_id')->comment("refers to users table");
            $table->tinyInteger('type')->default(1)->comment('1 => Video, 2 => Youtube, 3 => Vimeo');
            $table->string('title', 255)->comment('refers to title of podcast');
            $table->integer('duration')->comment('refers to duration of shorts in seconds');
            $table->Integer('view_count')->default(0)->comment('Video/Youtube count when user tap on that.');
            $table->string('tag', 255)->comment('tag for record');
            $table->string('deep_link_uri')->nullable()->comment('represents the deep link which redirects users to the shorts view on app');
            $table->timestamp('created_at')->useCurrent()->comment("date and time when record is created");
            $table->timestamp('updated_at')->useCurrent()->comment("date and time when record is updated");
            
            $table->foreign('category_id')
                ->references('id')
                ->on('categories')
                ->onDelete('CASCADE');
            
            $table->foreign('sub_category_id')
                ->references('id')
                ->on('sub_categories')
                ->onUpdate('CASCADE')
                ->onDelete('CASCADE');

            $table->foreign('tag_id')
                ->references('id')
                ->on('category_tags')
                ->onDelete('cascade');

            $table->foreign('author_id')
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
        Schema::dropIfExists('shorts');
        Schema::enableForeignKeyConstraints();
    }
}
