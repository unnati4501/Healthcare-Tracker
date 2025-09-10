<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateRecipesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('recipes', function (Blueprint $table) {
            $table->bigIncrements('id')->comment("primary key of the current table");

            $table->unsignedBigInteger('recipe_category_id')->nullable()->comment("refers to recipe_categories table");
            $table->unsignedBigInteger('creator_id')->comment("refers to users table - creator of recipe");
            
            $table->string('title', 255)->comment('title of recipe');
            $table->bigInteger('calories')->comment('calories we can get from recipe - kcal');
            $table->bigInteger('cooking_time')->comment('preperation time of recipe - seconds');
            $table->string('servings', 500)->comment('servings of recipe');
            $table->text('directions')->comment('directions/steps to make recipe');
            
            $table->timestamp('created_at')->useCurrent()->comment("date and time when record is created");
            $table->timestamp('updated_at')->useCurrent()->comment("date and time when record is updated");

            $table->foreign('recipe_category_id')
                ->references('id')->on('recipe_categories')
                ->onDelete('cascade');
            $table->foreign('creator_id')
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
        Schema::dropIfExists('recipes');
        Schema::enableForeignKeyConstraints();
    }
}
