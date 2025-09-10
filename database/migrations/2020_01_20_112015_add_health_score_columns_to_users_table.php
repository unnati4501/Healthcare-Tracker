<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddHealthScoreColumnsToUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('hs_show_banner')->default(0)->after('remember_token');
            $table->boolean('hs_remind_survey')->default(0)->after('hs_show_banner');
            $table->dateTime('hs_reminded_at')->nullable()->after('hs_remind_survey');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('hs_show_banner');
            $table->dropColumn('hs_remind_survey');
            $table->dropColumn('hs_reminded_at');
        });
    }
}
