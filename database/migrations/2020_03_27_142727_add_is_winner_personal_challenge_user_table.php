<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddIsWinnerPersonalChallengeUserTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('personal_challenge_users', function (Blueprint $table) {
            $table->boolean('is_winner')->default(false)->after('completed')->comment("default false, flag sets to true when user wins the challenge");
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('personal_challenge_users', function (Blueprint $table) {
            $table->dropColumn('is_winner');
        });
    }
}
