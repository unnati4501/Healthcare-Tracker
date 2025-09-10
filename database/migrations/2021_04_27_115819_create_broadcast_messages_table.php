<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBroadcastMessagesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('broadcast_messages', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('company_id')->nullable()->comment("refers to companies table");
            $table->unsignedBigInteger('user_id')->comment("refers to users table");
            $table->unsignedBigInteger('group_id')->comment("refers to groups table")->index();
            $table->string('group_type', 100)->comment("type of the group");
            $table->enum('type', ['instant', 'scheduled'])
                ->comment("instant - message will be broadcast instantly; scheduled - message will be broadcast at scheduled date and time")
                ->index();
            $table->string('title', 255)->comment("title of the broadcast");
            $table->longText('message')->comment("message of the broadcast");
            $table->enum('status', ['1', '2', '3'])->comment("1 - Pending; 2 - Broadcasted; 3 - Cancelled")->index();
            $table->timestamp('scheduled_at')->nullable()->comment("date and time when broadcast message is scheduled at");
            $table->timestamp('created_at')->useCurrent()->comment("date and time when record is created");
            $table->timestamp('updated_at')->useCurrent()->comment("date and time when record is updated");

            // adding cardinalities
            $table->foreign('company_id')
                ->references('id')
                ->on('companies')
                ->onDelete('cascade');
            $table->foreign('user_id')
                ->references('id')
                ->on('users')
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
        Schema::dropIfExists('broadcast_messages');
        Schema::enableForeignKeyConstraints();
    }
}
