<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateWsClientNotes extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ws_client_notes', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('cronofy_schedule_id')->nullable()->comment("refers to the cronofy schedule table");
            $table->unsignedBigInteger('user_id')->nullable()->comment("refers to the users table");
            $table->longText('comment')->nullable()->comment("notes added by ws client");
            $table->foreign('cronofy_schedule_id')
                ->references('id')
                ->on('cronofy_schedule')
                ->onDelete('CASCADE');
            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->onDelete('CASCADE');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('ws_client_notes');
    }
}
