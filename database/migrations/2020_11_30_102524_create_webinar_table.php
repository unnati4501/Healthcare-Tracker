<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateWebinarTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('webinar', function (Blueprint $table) {
            $table->bigIncrements('id')->comment("primary key of the current table");
            $table->unsignedBigInteger('category_id')->default(7)->comment("refers to categories table");
            $table->unsignedBigInteger('sub_category_id')->nullable()->comment("refers to sub_categories table");
            $table->unsignedBigInteger('author_id')->comment("refers to users table");
            $table->tinyInteger('type')->default(1)->comment('1 => Video, 2 => Youtube');
            $table->string('title', 255)->comment('refers to title of webinar');
            $table->integer('duration')->comment('refers to duration of webinar in seconds');
            $table->Integer('view_count')->default(0)->comment('Video/Youtube count when user tap on that.');
            $table->string('deep_link_uri')->nullable()->comment('represents the deep link which redirects users to the webinar view on portal/app');
            $table->timestamp('created_at')->useCurrent()->comment("date and time when record is created");
            $table->timestamp('updated_at')->useCurrent()->comment("date and time when record is updated");
            $table->foreign('category_id')
                ->references('id')
                ->on('categories')
                ->onUpdate('CASCADE')
                ->onDelete('CASCADE');
            $table->foreign('sub_category_id')
                ->references('id')
                ->on('sub_categories')
                ->onUpdate('CASCADE')
                ->onDelete('CASCADE');
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
        Schema::dropIfExists('webinar');
        Schema::enableForeignKeyConstraints();
    }
}
