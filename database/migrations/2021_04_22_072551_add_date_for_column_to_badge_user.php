<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddDateForColumnToBadgeUser extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('badge_user', function (Blueprint $table) {
            $table->timestamp('date_for')->nullable()->default(null)->comment('Date for badge is being awarded')->after('level');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('badge_user', function (Blueprint $table) {
            $table->dropColumn('date_for');
        });
    }
}
