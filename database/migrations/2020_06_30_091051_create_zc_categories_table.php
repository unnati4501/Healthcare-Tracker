<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateZcCategoriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('zc_categories', function (Blueprint $table) {
            $table->bigIncrements('id')->comment("primary key of the current table");
            $table->string('name', 255)->comment('name of category');
            $table->string('display_name', 255)->comment('display name of category');
            $table->boolean('status')->default(1)->comment('1 => Active, 0 => Inactive status of category ');
            $table->boolean('default')->default(0)->comment("if category is default - inserted from system admin/database seeder");
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
        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('zc_categories');
        Schema::enableForeignKeyConstraints();
    }
}
