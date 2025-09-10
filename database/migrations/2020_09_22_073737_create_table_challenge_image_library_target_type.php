<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTableChallengeImageLibraryTargetType extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('challenge_image_library_target_type', function (Blueprint $table) {
            $table->bigIncrements('id')->comment("primary key of the current table");
            $table->string('target')->nullable()->comment('name of the target type');
            $table->string('slug')->nullable()->comment('slug of the target type');
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
        Schema::dropIfExists('challenge_image_library_target_type');
        Schema::enableForeignKeyConstraints();
    }
}
