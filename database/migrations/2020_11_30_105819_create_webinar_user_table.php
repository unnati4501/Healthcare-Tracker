<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateWebinarUserTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('webinar_user', function (Blueprint $table) {
            $table->bigIncrements('id')->comment("primary key of the current table");

            $table->unsignedBigInteger('webinar_id')->nullable()->comment("refers to webinar table");
            $table->unsignedBigInteger('user_id')->comment("refers to users table");

            $table->boolean('saved')->default(false)->comment('true, if webinar is saved by user');
            $table->timestamp('saved_at')->nullable()->comment('store date at which user saved webinar.');
            $table->boolean('liked')->default(false)->comment('true, if webinar is liked by user');
            $table->timestamp('liked_at')->nullable()->comment('store date at which user liked webinar.');
            $table->boolean('favourited')->default(false)->comment('true, if webinar is liked by user');
            $table->timestamp('favourited_at')->nullable()->comment('store date at which user favourited webinar.');
            $table->Integer('view_count')->default(0)->comment('Video/Youtube count when user tap on that.');
            $table->timestamp('created_at')->useCurrent()->comment("date and time when record is created");
            $table->timestamp('updated_at')->useCurrent()->comment("date and time when record is updated");

            $table->foreign('webinar_id')
                ->references('id')->on('webinar')
                ->onDelete('cascade');
            $table->foreign('user_id')
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
        Schema::dropIfExists('webinar_user');
        Schema::enableForeignKeyConstraints();
    }
}
