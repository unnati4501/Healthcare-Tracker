<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddForeignKeysToRecipeTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('recipe', function (Blueprint $table) {
            $table->foreign('creator_id')->references('id')->on('users')->onUpdate('CASCADE')->onDelete('CASCADE');
            $table->foreign('chef_id')->references('id')->on('users')->onUpdate('CASCADE')->onDelete('CASCADE');
            $table->foreign('company_id')->references('id')->on('companies')->onUpdate('CASCADE')->onDelete('CASCADE');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('recipe', function (Blueprint $table) {
            $table->dropForeign('recipe_chef_id_foreign');
            $table->dropForeign('recipe_company_id_foreign');
            $table->dropForeign('recipe_creator_id_foreign');
        });
    }
}
