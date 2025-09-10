<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddFavouritedColumnsRecipeUserTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('recipe_user', function (Blueprint $table) {
            $table->boolean('favourited')->after('liked')->default(false)->comment('true, if recipe is favourited by user');
            $table->timestamp('favourited_at')->after('favourited')->nullable()->comment('store date at which user favourited recipe.');
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
            $table->dropColumn('favourited');
            $table->dropColumn('favourited_at');
        });
    }
}
