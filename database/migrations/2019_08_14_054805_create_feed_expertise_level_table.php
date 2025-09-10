<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateFeedExpertiseLevelTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('feed_expertise_level', function (Blueprint $table) {
            $table->bigIncrements('id')->comment("primary key of current table");

            $table->unsignedBigInteger('feed_id')->comment("refers to feeds table");
            $table->unsignedBigInteger('category_id')->comment("refers to categories table");

            $table->string('expertise_level', 255)->default('beginner')->comment('feed expertise level for the current category');

            $table->timestamp('created_at')->useCurrent()->comment("date and time when record is created");
            $table->timestamp('updated_at')->useCurrent()->comment("date and time when record is updated");

            $table->foreign('feed_id')
                ->references('id')->on('feeds')
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
        Schema::dropIfExists('feed_expertise_level');
        Schema::enableForeignKeyConstraints();
    }
}
