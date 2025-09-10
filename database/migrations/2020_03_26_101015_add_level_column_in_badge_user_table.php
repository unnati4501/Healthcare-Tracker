<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddLevelColumnInBadgeUserTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('badge_user', function (Blueprint $table) {
            $table->integer('level')->default(0)->comment('store user badge earned level')->after('expired_at');
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
            $table->dropColumn('level');
        });
    }
}
