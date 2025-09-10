<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateNewRecipeTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('recipe', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('creator_id')->index('creator_id')->nullable()->comment("refers to users table - creator of the recipe");
            $table->unsignedBigInteger('chef_id')->index('chef_id')->nullable()->comment("refers to users table - chef of the recipe");
            $table->unsignedBigInteger('company_id')->index('company_id')->nullable()->comment("refers to companies table - creator of the recipe");
            $table->string('title', 255)->comment('recipe title');
            $table->text('description')->nullable()->comment('recipe description');
            $table->string('image', 255)->nullable()->comment('recipe image');
            $table->float('calories', 15, 2)->nullable()->comment('calories of recipe');
            $table->time('cooking_time')->comment('cooking time taken for recipe format=H:i:s');
            $table->integer('servings')->comment('recipe to serve number of people');
            $table->json('ingredients')->comment('ingredients for recipe');
            $table->json('nutritions')->comment('recipe to serve number of peope');
            $table->string('deep_link_uri')->comment('represents the deep link which redirects users to the recipe view on app');
            $table->boolean('status')->default(1)->comment('1 => Active, 0 => Inactive status of recipe');
            $table->timestamp('created_at')->useCurrent()->comment("date and time when record is created");
            $table->timestamp('updated_at')->useCurrent()->comment("date and time when record is updated");
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('recipe');
    }
}
