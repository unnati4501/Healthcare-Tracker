<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateChallengeCategoriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('challenge_categories', function (Blueprint $table) {
            $table->bigIncrements('id')->comment("primary key of current table");
            
            $table->string('name', 255)->comment("Display name of the challenge category");
            $table->string('short_name', 255)->comment("slug for the challenge category");
            $table->boolean('is_excluded')->default(0)->comment("1 if category is excluded in challenge API response else 0");
            
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
        Schema::dropIfExists('challenge_categories');
        Schema::enableForeignKeyConstraints();
    }
}
