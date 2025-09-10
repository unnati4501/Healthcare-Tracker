<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class UpdateMediaDiskToAzure extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // if (Schema::hasTable('media')) {
        //     DB::statement("UPDATE `media` SET `disk` = 'azure' WHERE `media`.`disk` = 'spaces';");
        // }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // if (Schema::hasTable('media')) {
        //     DB::statement("UPDATE `media` SET `disk` = 'spaces' WHERE `media`.`disk` = 'azure';");
        // }
    }
}
