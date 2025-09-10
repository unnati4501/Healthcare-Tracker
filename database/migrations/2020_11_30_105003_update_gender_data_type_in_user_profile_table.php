<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateGenderDataTypeInUserProfileTable extends Migration
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
            DB::statement("ALTER TABLE `user_profile` CHANGE `gender` `gender` ENUM('male','female','other') NOT NULL DEFAULT 'male' COMMENT 'gender of user male/female/other';");
            // $table->enum('gender', ['male', 'female', 'other'])->default('male')->comment('gender of user male/female/other')->change();
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
            $table->string('gender')->nullable()->comment("gender of user male/female")->change();
        });
    }
}
