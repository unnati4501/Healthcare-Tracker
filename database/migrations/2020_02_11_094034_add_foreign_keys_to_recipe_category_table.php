<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddForeignKeysToRecipeCategoryTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('recipe_category', function (Blueprint $table) {
            $table->foreign('recipe_id')->references('id')->on('recipe')->onUpdate('CASCADE')->onDelete('CASCADE');
            $table->foreign('recipe_category_id')->references('id')->on('companies')->onUpdate('CASCADE')->onDelete('CASCADE');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('recipe_category', function (Blueprint $table) {
            $table->dropForeign('recipe_category_recipe_category_id_foreign');
            $table->dropForeign('recipe_category_recipe_id_foreign');
        });
    }
}
