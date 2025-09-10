<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSessionGroupUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('session_group_users', function (Blueprint $table) {
            $table->bigIncrements('id')->comment("primary key of the current table");
            $table->unsignedBigInteger('session_id')->nullable()->comment("refers to cronofy session table");
            $table->unsignedBigInteger('user_id')->nullable()->comment("refers to user table");
            $table->boolean('is_cancelled')->default(false)->comment("Invitation cancel by user or not.");
            $table->timestamp('created_at')->useCurrent()->comment("date and time when record is created");
            $table->timestamp('updated_at')->useCurrent()->comment("date and time when record is updated");

            $table->foreign('session_id')
                ->references('id')->on('cronofy_schedule')
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
        Schema::dropIfExists('session_group_users');
        Schema::enableForeignKeyConstraints();
    }
}
