<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateChallengeRulesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('challenge_rules', function (Blueprint $table) {
            $table->bigIncrements('id')->comment("primary key of current table");
            
            $table->unsignedBigInteger('challenge_id')->comment("refers to challenges table");
            $table->unsignedBigInteger('challenge_category_id')->comment("refers to challenge_categories table");
            $table->unsignedBigInteger('challenge_target_id')->comment("refers to challenge_targets table");

            $table->integer('target')->comment("target to complete the challenge");
            $table->string('uom', 255)->comment("unit of measurement to calculate the target");
            $table->integer('model_id')->nullable()->comment("model id to get extra fields like exercise id");
            $table->string('model_name', 255)->nullable()->comment("model name to get extra fields like exercise");
            
            $table->timestamp('created_at')->useCurrent()->comment("date and time when record is created");
            $table->timestamp('updated_at')->useCurrent()->comment("date and time when record is updated");

            $table->foreign('challenge_id')
                ->references('id')->on('challenges')
                ->onDelete('cascade');
            $table->foreign('challenge_category_id')
                ->references('id')->on('challenge_categories')
                ->onDelete('cascade');
            $table->foreign('challenge_target_id')
                ->references('id')->on('challenge_targets')
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
        Schema::dropIfExists('challenge_rules');
        Schema::enableForeignKeyConstraints();
    }
}
