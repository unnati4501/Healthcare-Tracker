<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddMetaFieldToEventRegisterUserLogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('event_registered_users_logs', function (Blueprint $table) {
            $table->json('meta')->nullable()->comment('To store meta data as JSON')->after('is_cancelled');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('event_registered_users_logs', function (Blueprint $table) {
            $table->dropColumn('meta');
        });
    }
}
