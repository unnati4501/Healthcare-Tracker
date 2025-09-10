<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class SetDefaultSubtitleForFeed extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('feeds', function (Blueprint $table) {
            \DB::statement("UPDATE `feeds` SET `feeds`.`subtitle` = 'Latest tips, hints and Wellbeing advice' WHERE `feeds`.`subtitle` IS NULL;");
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('feeds', function (Blueprint $table) {
            //
        });
    }
}
