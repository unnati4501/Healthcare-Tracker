<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMapLibraryTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('map_library', function (Blueprint $table) {
            $table->bigIncrements('id')->comment('Primary key of the current table');
            $table->string('name', 255)->comment('Name of map library');
            $table->text('description')->comment('Description of map library');
            $table->unsignedBigInteger('total_location')->nullable()->comment("number of total location");
            $table->string('total_distance')->nullable()->comment("number of total distance");
            $table->enum('status', [1, 2])->default(1)->comment("1 => InActive, 2 => Active");
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
        Schema::enableForeignKeyConstraints();
        Schema::dropIfExists('map_library');
        Schema::disableForeignKeyConstraints();
    }
}
