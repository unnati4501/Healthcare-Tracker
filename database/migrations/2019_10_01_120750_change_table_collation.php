<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ChangeTableCollation extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        \DB::query("ALTER TABLE `zevolife`.`user_coach_log` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
        \DB::query("ALTER TABLE `zevolife`.`user_course` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
