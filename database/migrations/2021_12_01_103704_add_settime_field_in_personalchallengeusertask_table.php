<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSettimeFieldInPersonalchallengeusertaskTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('personal_challenge_user_tasks', function (Blueprint $table) {
            $table->time('set_time')->nullable()->after('date')->comment("Set time for habit plan");
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('personal_challenge_user_tasks', function (Blueprint $table) {
            $table->dropColumn('set_time');
        });
    }
}
