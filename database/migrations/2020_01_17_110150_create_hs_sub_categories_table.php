<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateHsSubCategoriesTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('hs_sub_categories', function (Blueprint $table) {
            $table->bigIncrements('id')->comment("primary key of the current table");
            $table->string('name', 191)->comment('name of subcategory');
            $table->string('display_name', 191)->comment('display name of subcategory');
            $table->unsignedBigInteger('category_id')->index('category_id')->comment("refers to category table");
            $table->boolean('status')->default(1)->comment('1 => Active, 0 => Inactive status of subcategory ');
            $table->timestamp('created_at')->useCurrent()->comment("date and time when record is created");
            $table->timestamp('updated_at')->useCurrent()->comment("date and time when record is updated");
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('hs_sub_categories');
    }
}
