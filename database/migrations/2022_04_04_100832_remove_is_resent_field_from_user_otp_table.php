<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RemoveIsResentFieldFromUserOtpTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('user_otp', function (Blueprint $table) {
            $table->dropColumn('is_resent');
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
            $table->enum('is_resent', [0, 1])->default(0)->after('single_use_code')->comment("0 => Not resended, 1 => Resended");
        });
    }
}
