<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMeditationTracksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('meditation_tracks', function (Blueprint $table) {
            $table->bigIncrements('id')->comment("primary key of the current table");

            $table->unsignedBigInteger('meditation_category_id')->nullable()->comment("refers to meditation_categories table");
            $table->unsignedBigInteger('coach_id')->comment("refers to users table");
            
            $table->string('title', 255)->comment('refers to title of track');
            $table->integer('duration')->comment('refers to duration of track in seconds');
            $table->string('tag', 255)->comment('tag for record: move/nourish/inspire');
            
            $table->boolean('is_premium')->default(false)->comment('By deafult all tracks will be un-locked but super admin can change it to locked for premium content');

            $table->timestamp('created_at')->useCurrent()->comment("date and time when record is created");
            $table->timestamp('updated_at')->useCurrent()->comment("date and time when record is updated");

            $table->foreign('meditation_category_id')
                ->references('id')->on('meditation_categories')
                ->onDelete('cascade');
            $table->foreign('coach_id')
                ->references('id')->on('users')
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
        Schema::dropIfExists('meditation_tracks');
        Schema::enableForeignKeyConstraints();
    }
}
