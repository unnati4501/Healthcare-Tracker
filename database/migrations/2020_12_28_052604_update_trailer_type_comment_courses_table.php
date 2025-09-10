<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateTrailerTypeCommentCoursesTable extends Migration
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
        Schema::table('courses', function (Blueprint $table) {
            DB::statement("ALTER TABLE `courses` MODIFY COLUMN `trailer_type` ENUM('0','1','2','3')  NOT NULL DEFAULT '0' COMMENT ' tailer type Disabled/Audio/Video/Youtube'");
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('courses', function (Blueprint $table) {
            $table->string('trailer_type')->nullable()->comment("0 => Disabled, 1 => Audio, 2 => Video, 3=> Youtube")->change();
        });
    }
}
