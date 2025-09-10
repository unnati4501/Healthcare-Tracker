<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCronLogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cron_logs', function (Blueprint $table) {
            $table->bigIncrements('id')->comment("primary key of current table");
            $table->string('cron_name')->nullable()->comment('name of cron');
            $table->string('unique_key')->nullable()->comment('unique string');
            $table->tinyInteger('is_exception')->default(0)->comment('to check if there was any exception');
            $table->string('log_desc')->nullable()->comment('description of log if required');
            $table->dateTime('start_time')->comment('start time of cron');
            $table->dateTime('end_time')->nullable()->comment('end time of cron');
            $table->time('execution_time')->nullable()->comment('execution time of cron format=H:i:s');
            $table->nullableTimestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('cron_logs');
    }
}
