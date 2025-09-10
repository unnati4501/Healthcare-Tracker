<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddForeignKeysToHsSubCategoriesTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('hs_sub_categories', function (Blueprint $table) {
            $table->foreign('category_id')->references('id')->on('hs_categories')->onUpdate('CASCADE')->onDelete('CASCADE');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('hs_sub_categories', function (Blueprint $table) {
            $table->dropForeign('hs_sub_categories_category_id_foreign');
        });
    }
}
