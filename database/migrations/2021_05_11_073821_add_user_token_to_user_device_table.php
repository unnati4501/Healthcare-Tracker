<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddUserTokenToUserDeviceTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('user_device', function (Blueprint $table) {
            $table->longText('user_token')->nullable()->after('device_token')->comment("user token from tracker");
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('user_device', function (Blueprint $table) {
            $table->dropColumn('user_token');
        });
    }
}
