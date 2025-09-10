<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateTrailerTypeFieldInCourseLessionsTable extends Migration
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
        Schema::table('course_lessions', function (Blueprint $table) {
            DB::statement("ALTER TABLE `course_lessions` MODIFY COLUMN `type` ENUM('1','2','3','4','5')  NOT NULL COMMENT '1 => Audio, 2 => Video, 3 => Youtube Link, 4 => Content, 5 => Vimeo'");
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('course_lessions', function (Blueprint $table) {
            DB::statement("ALTER TABLE `course_lessions` MODIFY COLUMN `type` ENUM('1','2','3','4')  NOT NULL COMMENT '1 => Audio, 2 => Video, 3 => Youtube Link, 4 => Content'");
        });
    }
}
