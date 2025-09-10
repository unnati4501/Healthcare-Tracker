<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateHsCategoriesTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('hs_categories', function (Blueprint $table) {
            $table->bigIncrements('id')->comment("primary key of the current table");
            $table->string('name', 191)->comment('name of category');
            $table->string('display_name', 191)->comment('display name of category');
            $table->boolean('status')->default(1)->comment('1 => Active, 0 => Inactive status of category ');
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
        Schema::drop('hs_categories');
    }
}
