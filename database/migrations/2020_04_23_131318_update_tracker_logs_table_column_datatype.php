<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UpdateTrackerLogsTableColumnDatatype extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tracker_logs', function (Blueprint $table) {
            $table->text('request_url')->nullable()->default(null)->comment('tracker\'s request URL')->change();
            $table->mediumText('request_data')->nullable()->default(null)->comment('tracker\'s request Data')->change();
            $table->mediumText('fetched_data')->nullable()->default(null)->comment('Response from racker')->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('tracker_logs', function (Blueprint $table) {
            $table->string('request_url', 255)->nullable()->default(null)->comment('tracker\'s request URL')->change();
            $table->string('request_data', 255)->nullable()->default(null)->comment('tracker\'s request Data')->change();
            $table->string('fetched_data', 255)->nullable()->default(null)->comment('Response from racker')->change();
        });
    }
}
