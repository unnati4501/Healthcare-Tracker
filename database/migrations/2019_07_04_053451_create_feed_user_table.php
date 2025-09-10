<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateFeedUserTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('feed_user', function (Blueprint $table) {
            $table->bigIncrements('id')->comment("primary key of the current table");

            $table->unsignedBigInteger('feed_id')->nullable()->comment("refers to feeds table");
            $table->unsignedBigInteger('user_id')->nullable()->comment("refers to users table");
            
            $table->boolean('saved')->default(false)->comment('true, if feed is saved by user');
            $table->boolean('liked')->default(false)->comment('true, if feed is liked by user');
            
            $table->timestamp('created_at')->useCurrent()->comment("date and time when record is created");
            $table->timestamp('updated_at')->useCurrent()->comment("date and time when record is updated");

            $table->foreign('user_id')
                ->references('id')->on('users')
                ->onDelete('cascade');
            $table->foreign('feed_id')
                ->references('id')->on('feeds')
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
        Schema::dropIfExists('feed_user');
        Schema::enableForeignKeyConstraints();
    }
}
