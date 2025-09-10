<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateTrailerTypeFieldInCourseTable extends Migration
{
    public function __construct()
    {
        DB::getDoctrineSchemaManager()->getDatabasePlatform()->registerDoctrineTypeMapping('enum', 'string');
    }
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('course', function (Blueprint $table) {
            DB::statement("ALTER TABLE `courses` MODIFY COLUMN `trailer_type` ENUM('0','1','2','3','4')  NOT NULL DEFAULT '0' COMMENT ' tailer type Disabled/Audio/Video/Youtube/Vimeo'");
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('course', function (Blueprint $table) {
            DB::statement("ALTER TABLE `courses` MODIFY COLUMN `trailer_type` ENUM('0','1','2','3')  NOT NULL DEFAULT '0' COMMENT ' tailer type Disabled/Audio/Video/Youtube'");
        });
    }
}
