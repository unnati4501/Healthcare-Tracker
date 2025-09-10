<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTrackerLogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tracker_logs', function (Blueprint $table) {
            $table->bigIncrements('id')->comment("primary key of the current table");
            $table->unsignedBigInteger('user_id')->comment("refers to users table");
            $table->string('os', 255)->comment('Requested from OS');
            $table->string('tracker_name', 255)->comment('tracker\'s Name');
            $table->string('request_url', 255)->nullable()->default(null)->comment('tracker\'s request URL');
            $table->string('request_data', 255)->nullable()->default(null)->comment('tracker\'s request Data');
            $table->string('fetched_data', 255)->nullable()->default(null)->comment('Response from racker');
            $table->timestamp('created_at')->useCurrent()->comment("date and time when record is created");

            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->onUpdate('CASCADE')
                ->onDelete('CASCADE');
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
        Schema::dropIfExists('tracker_logs');
        Schema::enableForeignKeyConstraints();
    }
}
