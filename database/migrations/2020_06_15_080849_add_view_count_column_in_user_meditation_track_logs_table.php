<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddViewCountColumnInUserMeditationTrackLogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('user_meditation_track_logs', function (Blueprint $table) {
            $table->Integer('view_count')->default(0)->after('favourited')->comment('Audio/Video count when user tep no that.');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('user_meditation_track_logs', function (Blueprint $table) {
            $table->dropColumn('view_count');
        });
    }
}
