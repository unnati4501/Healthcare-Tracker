<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddIsResentFielsToUsersOtpTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('user_otp', function (Blueprint $table) {
            $table->bigInteger('is_resent')->default(0)->after('single_use_code');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('user_otp', function (Blueprint $table) {
            $table->dropColumn('is_resent');
        });
    }
}
