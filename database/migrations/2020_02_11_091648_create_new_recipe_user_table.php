<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateNewRecipeUserTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('recipe_user', function (Blueprint $table) {
            $table->bigIncrements('id')->comment("primary key of the current table");
            $table->unsignedBigInteger('recipe_id')->index('recipe_id')->comment("refers to recipe table");
            $table->unsignedBigInteger('user_id')->index('user_id')->comment("refers to users table");
            $table->boolean('saved')->default(false)->comment('true, if recipe is saved by user');
            $table->timestamp('saved_at')->nullable()->comment('store date at which user saved recipe.');
            $table->boolean('liked')->default(false)->comment('true, if recipe is liked by user');
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
        Schema::dropIfExists('recipe_user');
    }
}
