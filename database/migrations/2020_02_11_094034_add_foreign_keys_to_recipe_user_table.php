<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddForeignKeysToRecipeUserTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('recipe_user', function (Blueprint $table) {
            $table->foreign('recipe_id')->references('id')->on('recipe')->onUpdate('CASCADE')->onDelete('CASCADE');
            $table->foreign('user_id')->references('id')->on('users')->onUpdate('CASCADE')->onDelete('CASCADE');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('recipe_user', function (Blueprint $table) {
            $table->dropForeign('recipe_user_recipe_id_foreign');
            $table->dropForeign('recipe_user_user_id_foreign');
        });
    }
}
