<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateGroupMessagesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('group_messages', function (Blueprint $table) {
            $table->bigIncrements('id')->comment("primary key of the current table");

            $table->unsignedBigInteger('user_id')->comment("refers to users table- member of the group");
            $table->unsignedBigInteger('group_id')->comment("refers to groups table");
            $table->unsignedBigInteger('group_message_id')->nullable()->comment('parent message id');

            $table->string('message', 255)->nullable()->comment('message sent by member of the group');
            $table->bigInteger('model_id')->nullable()->comment('model_id sent by member of the group with share functionality - course, meditation, recipe');
            $table->string('model_name', 255)->nullable()->comment('model_name sent by member of the group with share functionality - course, meditation, recipe');
            
            $table->timestamp('created_at')->useCurrent()->comment("date and time when record is created");
            $table->timestamp('updated_at')->useCurrent()->comment("date and time when record is updated");

            $table->foreign('user_id')
                ->references('id')->on('users')
                ->onDelete('cascade');
            $table->foreign('group_id')
                ->references('id')->on('groups')
                ->onDelete('cascade');
            $table->foreign('group_message_id')
                ->references('id')->on('group_messages')
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
        Schema::dropIfExists('group_messages');
        Schema::enableForeignKeyConstraints();
    }
}
