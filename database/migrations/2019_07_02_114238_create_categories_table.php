<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCategoriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('categories', function (Blueprint $table) {
            $table->bigIncrements('id')->comment("primary key of current table");
            
            $table->string('name', 255)->comment("Display name of the category");
            $table->string('short_name', 255)->comment("slug of the category");
            $table->string('description', 255)->nullable()->comment("Description of the category");
            $table->boolean('in_activity_level')->default(0)->comment("if category needs to be displayed in activity level");
            $table->boolean('is_excluded')->default(0)->comment("1 if category is excluded in course API response else 0");
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
        Schema::dropIfExists('categories');
        Schema::enableForeignKeyConstraints();
    }
}
