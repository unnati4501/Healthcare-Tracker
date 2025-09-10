<?php

use App\Http\Traits\DisableForeignKeys;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSubCategoryIdFeedsTable extends Migration
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

        Schema::table('feeds', function (Blueprint $table) {
            $table->unsignedBigInteger('category_id')->after('company_id')->default(2)->comment("refers to categories table");
            $table->unsignedBigInteger('sub_category_id')->after('category_id')->nullable()->comment("refers to sub_categories table");

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

        Schema::table('feeds', function (Blueprint $table) {
            $table->dropForeign('feeds_category_id_foreign');
            $table->dropForeign('feeds_sub_category_id_foreign');
            $table->dropColumn('category_id');
            $table->dropColumn('sub_category_id');
        });

        // Enable foreign key checks!
        $this->enableForeignKeys();
    }
}
