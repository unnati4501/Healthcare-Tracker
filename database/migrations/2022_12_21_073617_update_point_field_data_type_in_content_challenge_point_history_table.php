<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdatePointFieldDataTypeInContentChallengePointHistoryTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('content_challenge_point_history', function (Blueprint $table) {
            $table->float('points', 10, 0)->comment('points synced - count')->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('content_challenge_point_history', function (Blueprint $table) {
            $table->bigInteger('points')->comment('points synced - count')->change();
        });
    }
}
