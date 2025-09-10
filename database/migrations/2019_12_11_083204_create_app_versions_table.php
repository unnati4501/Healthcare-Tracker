<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAppVersionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('app_versions', function (Blueprint $table) {
            $table->bigIncrements('id')->comment("primary key of the current table");
            
            $table->double('andriod_version')->comment('version of andriod device');
            $table->boolean('andriod_force_update')->default(false)->comment('andriod force update is required for respective version');
            $table->double('ios_version')->comment('version of ios device');
            $table->boolean('ios_force_update')->default(false)->comment('ios force update is required for respective version');

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
        Schema::dropIfExists('app_versions');
        Schema::enableForeignKeyConstraints();
    }
}
