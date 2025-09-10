<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMapPropertiesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('map_properties', function (Blueprint $table) {
            $table->bigIncrements('id')->comment("primary key of the current table");
            $table->unsignedBigInteger('map_id')->nullable()->comment("refers to map library table");
            $table->json('properties')->nullable()->comment('To store properties data as JSON like properties details like lat/long, name, distance etc');
            $table->timestamp('created_at')->useCurrent()->comment("date and time when record is created");
            $table->timestamp('updated_at')->useCurrent()->comment("date and time when record is updated");
            $table->foreign('map_id')
                ->references('id')->on('map_library')
                ->onDelete('cascade');
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
        Schema::dropIfExists('map_properties');
        Schema::enableForeignKeyConstraints();
    }
}
