<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddMetaFieldToUserPointLogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('user_point_logs', function (Blueprint $table) {
            $table->json('meta')->nullable()->comment('To store meta data as JSON')->after('point');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('user_point_logs', function (Blueprint $table) {
            $table->dropColumn('meta');
        });
    }
}
