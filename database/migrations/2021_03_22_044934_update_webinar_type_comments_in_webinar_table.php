<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateWebinarTypeCommentsInWebinarTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('webinar', function (Blueprint $table) {
            DB::statement("ALTER TABLE `webinar` CHANGE `type` `type` tinyint(4) DEFAULT 1 COMMENT '1 => Video, 2 => Youtube, 3 => Vimeo'");
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('webinar', function (Blueprint $table) {
             DB::statement("ALTER TABLE `webinar` CHANGE `type` `type` tinyint(4) DEFAULT 1 COMMENT '1 => Video, 2 => Youtube'");
        });
    }
}
