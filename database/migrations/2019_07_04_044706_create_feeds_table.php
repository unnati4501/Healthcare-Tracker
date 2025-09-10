<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateFeedsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('feeds', function (Blueprint $table) {
            $table->bigIncrements('id')->comment("primary key of the current table");

            $table->unsignedBigInteger('creator_id')->nullable()->comment("refers to users table- creator of the feed");
            
            $table->string('expertise_level', 255)->default('beginner')->comment('feed expertise level for the current category - visiblity purpose');
            $table->string('title', 255)->comment('feed title');
            $table->text('description')->comment('feed description');
            $table->string('tag', 255)->comment('tag for record: move/nourish/inspire');
            
            $table->dateTime('start_date')->nullable()->comment('represents the start date time when feed should be visible to user');
            $table->string('start_time')->nullable()->comment('represents the start time when feed should be visible to user');
            $table->dateTime('end_date')->nullable()->comment('represents the end date and time after which feed should not be visible to user');
            $table->string('end_time')->nullable()->comment('represents the end time when feed should be visible to user');

            $table->string('deep_link_uri')->comment('represents the deep link which redirects users to the feed view on app');
            
            $table->timestamp('created_at')->useCurrent()->comment("date and time when record is created");
            $table->timestamp('updated_at')->useCurrent()->comment("date and time when record is updated");

            $table->foreign('creator_id')
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
        Schema::dropIfExists('feeds');
        Schema::enableForeignKeyConstraints();
    }
}
