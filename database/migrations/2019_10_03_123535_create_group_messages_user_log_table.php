<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateGroupMessagesUserLogTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('group_messages_user_log', function (Blueprint $table) {
            $table->bigIncrements('id')->comment("primary key of the current table");

            $table->unsignedBigInteger('group_message_id')->nullable()->comment('parent message id');
            $table->unsignedBigInteger('group_id')->nullable()->comment("refers to groups table");
            $table->unsignedBigInteger('user_id')->comment("refers to users table");

            $table->boolean('read')->default(false)->comment('true, if message is read by user');
            $table->boolean('deleted')->default(false)->comment('true, if message is deleted by user');
            $table->boolean('favourited')->default(false)->comment('true, if message is favourited by user');

            $table->timestamp('created_at')->useCurrent()->comment("date and time when record is created");
            $table->timestamp('updated_at')->useCurrent()->comment("date and time when record is updated");

            $table->foreign('group_message_id')
                ->references('id')->on('group_messages')
                ->onDelete('cascade');
            $table->foreign('group_id')
                ->references('id')->on('groups')
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
        Schema::dropIfExists('group_messages_user_log');
        Schema::enableForeignKeyConstraints();
    }
}
