<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateChallengeTargetsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('challenge_targets', function (Blueprint $table) {
            $table->bigIncrements('id')->comment("primary key of current table");
            
            $table->string('name', 255)->comment("Display name of the challenge target");
            $table->string('short_name', 255)->comment("slug for the challenge target");
            
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
        Schema::dropIfExists('challenge_targets');
        Schema::enableForeignKeyConstraints();
    }
}
