<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateRecipeCategoryTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('recipe_category', function (Blueprint $table) {
            $table->bigIncrements('id')->comment("primary key of the current table");

            $table->unsignedBigInteger('recipe_category_id')->nullable()->comment("refers to recipe_categories table");
            $table->unsignedBigInteger('recipe_id')->nullable()->comment("refers to recipes table");

            $table->timestamp('created_at')->useCurrent()->comment("date and time when record is created");
            $table->timestamp('updated_at')->useCurrent()->comment("date and time when record is updated");

            $table->foreign('recipe_category_id')
                ->references('id')->on('recipe_categories')
                ->onDelete('cascade');
            $table->foreign('recipe_id')
                ->references('id')->on('recipes')
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
        Schema::dropIfExists('recipe_category');
        Schema::enableForeignKeyConstraints();
    }
}
