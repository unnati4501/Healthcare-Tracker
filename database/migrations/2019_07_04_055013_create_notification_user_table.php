<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateNotificationUserTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('notification_user', function (Blueprint $table) {
            $table->bigIncrements('id')->comment("primary key of the current table");

            $table->unsignedBigInteger('notification_id')->nullable()->comment("refers to notifications table");
            $table->unsignedBigInteger('user_id')->nullable()->comment("refers to users table");
            
            $table->boolean('sent', true)->default(true)->comment('flag to identify notification sent to user or not');
            $table->boolean('read')->default(false)->comment('true, if notification is read by user');
            $table->timestamp('read_at')->nullable()->comment('datetime when user read notification');
            
            $table->timestamp('created_at')->useCurrent()->comment("date and time when record is created");
            $table->timestamp('updated_at')->useCurrent()->comment("date and time when record is updated");

            $table->foreign('user_id')
                ->references('id')->on('users')
                ->onDelete('cascade');
            $table->foreign('notification_id')
                ->references('id')->on('notifications')
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
        Schema::dropIfExists('notification_user');
        Schema::enableForeignKeyConstraints();
    }
}
