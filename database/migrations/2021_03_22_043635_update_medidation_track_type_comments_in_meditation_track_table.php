<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateMedidationTrackTypeCommentsInMeditationTrackTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('meditation_tracks', function (Blueprint $table) {
            DB::statement("ALTER TABLE `meditation_tracks` CHANGE `type` `type` tinyint(4) DEFAULT 1 COMMENT '1 => Audio, 2 => Video, 3 => Youtube, 4 => Vimeo'");
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
            DB::statement("ALTER TABLE `meditation_tracks` CHANGE `type` `type` tinyint(4) DEFAULT 1 COMMENT '1 => Audio, 2 => Video, 3 => Youtube'");
        });
    }
}
