<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateFitnessTypeFieldsPersonalChallenge extends Migration
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
        Schema::table('personal_challenges', function (Blueprint $table) {
            DB::statement("ALTER TABLE `personal_challenges` CHANGE `type` `type` ENUM('to-do', 'streak', 'steps', 'distance', 'meditations') DEFAULT 'to-do'");
            $table->enum('challenge_type', ['routine', 'challenge'])->default('routine')->after('duration');
            $table->integer('target_value')->default(0)->after('challenge_type')->comment("target to complete the challenge");
            $table->boolean('recursive')->default(false)->after('description')->comment("default false, admin can create challenge in repetition mode");
        });

        Schema::table('personal_challenge_users', function (Blueprint $table) {
            $table->integer('recursive_count')->default(0)->after('is_winner')->comment("count - how many times challenge shoule be automatically created");
            $table->boolean('recursive_completed')->default(0)->after('recursive_count')->comment('count - recurisve completed count');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('personal_challenges', function (Blueprint $table) {
            DB::statement("ALTER TABLE `personal_challenges` CHANGE `type` `type` ENUM('to-do', 'streak') DEFAULT 'to-do'");
            $table->dropColumn('challenge_type');
            $table->dropColumn('target_value');
            $table->dropColumn('recursive');
        });

        Schema::table('personal_challenge_users', function (Blueprint $table) {
            $table->dropColumn('recursive_count');
            $table->dropColumn('recursive_completed');
        });
    }
}
