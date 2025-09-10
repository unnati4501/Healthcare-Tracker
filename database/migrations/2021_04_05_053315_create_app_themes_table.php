<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAppThemesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('app_themes', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('slug', 255)->comment("Slug of theme");
            $table->string('name', 255)->comment("Name of theme");
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
        Schema::dropIfExists('app_themes');
        Schema::enableForeignKeyConstraints();
    }
}
