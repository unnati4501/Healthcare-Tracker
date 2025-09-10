<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateGenderNoneTypeInUserProfileTable extends Migration
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
        Schema::table('user_profile', function (Blueprint $table) {
            DB::statement("ALTER TABLE `user_profile` CHANGE `gender` `gender` ENUM('male','female','other','none') NOT NULL DEFAULT 'male' COMMENT 'gender of user male/female/other/prefer not to say';");
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('user_profile', function (Blueprint $table) {
            DB::statement("ALTER TABLE `user_profile` CHANGE `gender` `gender` ENUM('male','female','other') NOT NULL DEFAULT 'male' COMMENT 'gender of user male/female/other';");
        });
    }
}
