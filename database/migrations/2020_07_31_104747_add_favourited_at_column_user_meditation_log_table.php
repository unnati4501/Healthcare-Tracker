<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddFavouritedAtColumnUserMeditationLogTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('user_meditation_track_logs', function (Blueprint $table) {
            $table->timestamp('favourited_at')->after('favourited')->nullable()->comment('store date at which user favourited meditation track logs.');
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
            $table->dropColumn('favourited_at');
        });
    }
}
