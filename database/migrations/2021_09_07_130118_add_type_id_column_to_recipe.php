<?php

use App\Models\Recipe;
use App\Models\RecipeType;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddTypeIdColumnToRecipe extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('recipe', function (Blueprint $table) {
            $table->unsignedBigInteger('type_id')->nullable()->index('type_id')->comment("refers to recipe_types table")->after('tag_id');
            $table->foreign('type_id')
                ->references('id')
                ->on('recipe_types')
                ->onDelete('CASCADE');
        });

        // set veg as default type for all the recipes
        $type = RecipeType::select('id')->where('slug', 'veg')->first();
        if (!is_null($type)) {
            Recipe::query()->update([
                'type_id' => $type->id,
            ]);
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::disableForeignKeyConstraints();
        Schema::table('recipe', function (Blueprint $table) {
            $table->dropForeign('recipe_type_id_foreign');
            $table->dropColumn('type_id');
        });
        Schema::enableForeignKeyConstraints();
    }
}
