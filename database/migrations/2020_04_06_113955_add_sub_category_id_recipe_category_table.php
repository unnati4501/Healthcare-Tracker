<?php

use App\Http\Traits\DisableForeignKeys;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSubCategoryIdRecipeCategoryTable extends Migration
{
    use DisableForeignKeys;

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Disable foreign key checks!
        $this->disableForeignKeys();

        Schema::table('recipe_category', function (Blueprint $table) {
            $table->dropForeign('recipe_category_recipe_category_id_foreign');
            $table->renameColumn('recipe_category_id', 'sub_category_id');
        });

        Schema::table('recipe_category', function (Blueprint $table) {
            $table->unsignedBigInteger('category_id')->after('recipe_id')->default(5)->comment("refers to categories table");

            $table->foreign('category_id')
                ->references('id')
                ->on('categories')
                ->onUpdate('CASCADE')
                ->onDelete('CASCADE');
            $table->foreign('sub_category_id')
                ->references('id')
                ->on('sub_categories')
                ->onUpdate('CASCADE')
                ->onDelete('CASCADE');
        });

        // Enable foreign key checks!
        $this->enableForeignKeys();
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Disable foreign key checks!
        $this->disableForeignKeys();

        Schema::table('recipe_category', function (Blueprint $table) {
            $table->dropForeign('recipe_category_category_id_foreign');
            $table->dropForeign('recipe_category_sub_category_id_foreign');
            $table->dropColumn('category_id');
            $table->renameColumn('sub_category_id', 'recipe_category_id');
        });

        Schema::table('recipe_category', function (Blueprint $table) {
            $table->foreign('recipe_category_id')
                ->references('id')
                ->on('recipe_categories')
                ->onUpdate('CASCADE')
                ->onDelete('CASCADE');
        });

        // Enable foreign key checks!
        $this->enableForeignKeys();
    }
}
