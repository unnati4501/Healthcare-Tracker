<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddDailyStepsFieldInBadgeUserTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('badge_user', function (Blueprint $table) {
            $table->bigInteger('steps')->default(0)->comment('Daily target steps earn default steps')->after('date_for');
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
            $table->dropColumn('steps');
        });
    }
}
