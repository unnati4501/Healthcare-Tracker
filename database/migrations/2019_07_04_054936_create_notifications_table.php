<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateNotificationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('notifications', function (Blueprint $table) {
            $table->bigIncrements('id')->comment("primary key of the current table");

            $table->unsignedBigInteger('creator_id')->nullable()->comment("refers to users table- creator of the notification");

            $table->string('creator_timezone')->nullable()->default('Asia/Calcutta')->comment('timezone of creator at time of creating notification');
            $table->enum('type', ['Auto', 'Manual'])->default('Auto')->comment('specifies type of notification. It can be auto or manual');
            $table->string('title', 255)->comment('title of notification');
            $table->string('message', 255)->nullable()->comment('actual message of notification');
            $table->string('deep_link_uri')->nullable()->comment('redirect action url from click of notification');
            $table->timestamp('scheduled_at')->comment('datetime when notification should be sent to users');
            $table->boolean('push', true)->default(true)->comment('flag to identify notification send as push notification');

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
        Schema::dropIfExists('notifications');
        Schema::enableForeignKeyConstraints();
    }
}
