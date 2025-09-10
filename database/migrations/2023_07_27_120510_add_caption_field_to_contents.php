<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCaptionFieldToContents extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::statement("ALTER TABLE meditation_tracks ADD caption varchar(20) DEFAULT NULL COMMENT 'New or popular tags based on recently added or most like' AFTER audio_type");
        DB::statement("ALTER TABLE webinar ADD caption varchar(20) DEFAULT NULL COMMENT 'New or popular tags based on recently added or most liked/viewed' AFTER view_count");
        DB::statement("ALTER TABLE recipe ADD caption varchar(20) DEFAULT NULL  COMMENT 'New or popular tags based on recently added or most liked/viewed' AFTER view_count");
        DB::statement("ALTER TABLE feeds ADD caption varchar(20) DEFAULT NULL COMMENT 'New or popular tags based on recently added or most liked/viewed' AFTER view_count");
        DB::statement("ALTER TABLE podcasts ADD caption varchar(20) DEFAULT NULL COMMENT 'New or popular tags based on recently added or most liked/viewed' AFTER view_count");
        DB::statement("ALTER TABLE courses ADD caption varchar(20) DEFAULT NULL COMMENT 'New or popular tags based on recently added or most liked/viewed' AFTER status");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::statement("ALTER TABLE meditation_tracks DROP COLUMN caption");
        DB::statement("ALTER TABLE feeds DROP COLUMN caption");
        DB::statement("ALTER TABLE webinar DROP COLUMN caption");
        DB::statement("ALTER TABLE recipe DROP COLUMN caption");
        DB::statement("ALTER TABLE courses DROP COLUMN caption");
        DB::statement("ALTER TABLE podcasts DROP COLUMN caption");
    }
}
