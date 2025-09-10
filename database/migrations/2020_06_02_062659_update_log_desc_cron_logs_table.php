<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateLogDescCronLogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('cron_logs', function (Blueprint $table) {
            $table->longText('log_desc')->nullable()->comment('description of log if required')->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('cron_logs', function (Blueprint $table) {
            $table->string('log_desc')->nullable()->comment('description of log if required')->change();
        });
    }
}
