<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateChallengesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('challenges', function (Blueprint $table) {
            $table->bigIncrements('id')->comment("primary key of current table");
            
            $table->unsignedBigInteger('creator_id')->comment("refers to users table- creator of the challenge");
            $table->unsignedBigInteger('challenge_category_id')->comment("refers to challenge_categories table");
            // $table->unsignedBigInteger('challenge_target_id')->comment("refers to challenge_targets table");

            $table->unsignedBigInteger('parent_id')->nullable()->comment("refers to parent child relation of challenges");

            $table->string('timezone', 255)->comment("timezone in which challenge is created");
            $table->string('title', 255)->comment("title of the challenge");
            $table->string('description', 255)->nullable()->comment("description for the challenge");
            $table->dateTime('start_date')->comment('start date and time of challenge');
            $table->dateTime('end_date')->comment('end date and time of challenge');
            $table->boolean('close')->default(true)->comment("default true, user have to request to join for close the challenge");
            $table->boolean('finished')->default(false)->comment("default false, will be true when challenge gets completed");
            $table->boolean('recurring')->default(false)->comment("default false, admin can create challenge in repetition mode");
            $table->integer('recurring_count')->nullable()->comment("count - how many times challenge shoule be automatically created");
            $table->string('recurring_type', 255)->nullable()->comment("refers to recurring type weekly,biweekly,monthly ");
            
            $table->timestamp('created_at')->useCurrent()->comment("date and time when record is created");
            $table->timestamp('updated_at')->useCurrent()->comment("date and time when record is updated");

            $table->foreign('creator_id')
                ->references('id')->on('users')
                ->onDelete('cascade');
            $table->foreign('challenge_category_id')
                ->references('id')->on('challenge_categories')
                ->onDelete('cascade');
            // $table->foreign('challenge_target_id')
            //     ->references('id')->on('challenge_targets')
            //     ->onDelete('cascade');
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
        Schema::dropIfExists('challenges');
        Schema::enableForeignKeyConstraints();
    }
}
