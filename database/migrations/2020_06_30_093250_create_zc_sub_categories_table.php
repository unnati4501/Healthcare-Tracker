<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateZcSubCategoriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('zc_sub_categories', function (Blueprint $table) {
            $table->bigIncrements('id')->comment("primary key of the current table");
            $table->string('name', 255)->comment('name of subcategory');
            $table->string('display_name', 255)->comment('display name of subcategory');
            $table->unsignedBigInteger('category_id')->comment("refers to category table");
            $table->boolean('status')->default(1)->comment('1 => Active, 0 => Inactive status of subcategory ');
            $table->boolean('default')->default(0)->comment("if sub category is default - inserted from system admin/database seeder");
            $table->boolean('is_primum')->default(0)->comment("if sub category is primum or not");
            $table->timestamp('created_at')->useCurrent()->comment("date and time when record is created");
            $table->timestamp('updated_at')->useCurrent()->comment("date and time when record is updated");

            $table->foreign('category_id')
                ->references('id')
                ->on('zc_categories')
                ->onUpdate('CASCADE')
                ->onDelete('CASCADE');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('zc_sub_categories');
        Schema::enableForeignKeyConstraints();
    }
}
