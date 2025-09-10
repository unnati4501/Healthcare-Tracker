<?php

use App\Http\Traits\DisableForeignKeys;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

class DropRecipeTables extends Migration
{
    use DisableForeignKeys;

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $this->disableForeignKeys();

        Schema::dropIfExists('recipes');
        Schema::dropIfExists('recipe_categories');
        Schema::dropIfExists('recipe_category');
        Schema::dropIfExists('recipe_ingredients');
        Schema::dropIfExists('recipe_nutritions');
        Schema::dropIfExists('recipe_user_log');

        $this->enableForeignKeys();
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
