<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateMapLibraryStatusComment extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::statement("ALTER TABLE map_library MODIFY COLUMN status ENUM('1', '2', '3') NOT NULL DEFAULT '1' COMMENT '1 => InActive, 2 => Active, 3 => Archive'");

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::statement("ALTER TABLE map_library MODIFY COLUMN status ENUM('1', '2') NOT NULL DEFAULT '1' COMMENT '1 => InActive, 2 => Active'");
    }
}
