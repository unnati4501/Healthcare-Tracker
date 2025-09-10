<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateChallengeTypeFieldInPersonalChallengeTable extends Migration
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
        Schema::table('personal_challenge', function (Blueprint $table) {
            DB::statement("ALTER TABLE `personal_challenges` CHANGE `challenge_type` `challenge_type` ENUM('routine', 'challenge', 'habit') DEFAULT 'routine'");
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('personal_challenge', function (Blueprint $table) {
            DB::statement("ALTER TABLE `personal_challenges` CHANGE `challenge_type` `challenge_type` ENUM('routine', 'challenge') DEFAULT 'routine'");
        });
    }
}
