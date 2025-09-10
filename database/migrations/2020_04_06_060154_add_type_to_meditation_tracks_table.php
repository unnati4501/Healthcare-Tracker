<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddTypeToMeditationTracksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('meditation_tracks', function (Blueprint $table) {
            $table->tinyInteger('type')->default(1)->after('coach_id')->comment('1 => Audio, 2 => Video');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('meditation_tracks', function (Blueprint $table) {
            $table->dropColumn('type');
        });
    }
}
