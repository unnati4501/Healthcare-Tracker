<?php

use App\Http\Traits\DisableForeignKeys;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSubCategoryIdGroupsTable extends Migration
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

        Schema::table('groups', function (Blueprint $table) {
            $table->dropForeign('groups_category_id_foreign');
            $table->renameColumn('category_id', 'sub_category_id');
        });

        Schema::table('groups', function (Blueprint $table) {
            $table->unsignedBigInteger('category_id')->after('company_id')->default(3)->index('category_id')->comment("refers to sub_categories table");

            $table->foreign('sub_category_id')
                ->references('id')
                ->on('sub_categories')
                ->onUpdate('CASCADE')
                ->onDelete('CASCADE');
        });

        Schema::table('groups', function (Blueprint $table) {
            $table->foreign('category_id')
                ->references('id')
                ->on('categories')
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

        Schema::table('groups', function (Blueprint $table) {
            $table->dropForeign('groups_category_id_foreign');
            $table->dropForeign('groups_sub_category_id_foreign');
            $table->dropColumn('category_id');
            $table->renameColumn('sub_category_id', 'category_id');
        });

        Schema::table('groups', function (Blueprint $table) {
            $table->foreign('category_id')
                ->references('id')
                ->on('categories')
                ->onUpdate('CASCADE')
                ->onDelete('CASCADE');
        });

        // Enable foreign key checks!
        $this->enableForeignKeys();
    }
}
