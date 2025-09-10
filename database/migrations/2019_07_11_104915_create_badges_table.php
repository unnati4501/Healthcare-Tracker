<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateBadgesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('badges', function (Blueprint $table) {
            $table->bigIncrements('id')->comment("primary key of current table");
            
            $table->unsignedBigInteger('creator_id')->comment("refers to users table- creator of the badge");
            $table->unsignedBigInteger('challenge_category_id')->nullable()->comment("refers to challenge_categories table");
            $table->unsignedBigInteger('challenge_target_id')->nullable()->comment("refers to challenge_targets table");

            $table->string('type', 255)->comment("badge type - challenge, general, course");
            $table->string('title', 255)->comment("title of the badge");
            $table->string('description')->nullable()->comment("description for the badge");
            $table->boolean('can_expire')->default(false)->comment("default false,badge will not be expired from user timeline");
            $table->integer('expires_after')->nullable()->comment("days count afterwhich badge should be expired if can_expire is true");
            $table->integer('target')->comment("target to complete the challenge");
            $table->string('uom', 255)->nullable()->comment("unit of measurement to calculate the target");
            $table->integer('model_id')->nullable()->comment("model id to get extra fields like exercise id");
            $table->string('model_name', 255)->nullable()->comment("model name to get extra fields like exercise");
            
            $table->timestamp('created_at')->useCurrent()->comment("date and time when record is created");
            $table->timestamp('updated_at')->useCurrent()->comment("date and time when record is updated");

            $table->foreign('creator_id')
                ->references('id')->on('users')
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
        Schema::dropIfExists('badges');
        Schema::enableForeignKeyConstraints();
    }
}
