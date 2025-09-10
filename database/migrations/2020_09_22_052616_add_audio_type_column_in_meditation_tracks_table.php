<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddAudioTypeColumnInMeditationTracksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('meditation_tracks', function (Blueprint $table) {
            $table->tinyInteger('audio_type')->default(null)->after('view_count')->comment('1 => music, 2 => vocal');
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
            $table->dropColumn('audio_type');
        });
    }
}
