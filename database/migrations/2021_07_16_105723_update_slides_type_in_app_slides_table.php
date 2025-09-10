<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateSlidesTypeInAppSlidesTable extends Migration
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
        Schema::table('app_slides', function (Blueprint $table) {
            DB::statement("ALTER TABLE `app_slides` CHANGE `type` `type` ENUM('app', 'portal', 'eap') DEFAULT 'app'");
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('app_slides', function (Blueprint $table) {
            DB::statement("ALTER TABLE `app_slides` CHANGE `type` `type` ENUM('app', 'portal') DEFAULT 'app'");
        });
    }
}
