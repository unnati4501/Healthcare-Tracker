<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddCompanyIdFreezedChallengeParticipants extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('freezed_challenge_participents', function (Blueprint $table) {
            $table->unsignedBigInteger('company_id')->after('team_id')->nullable()->comment("refers to companies table");
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('freezed_challenge_participents', function (Blueprint $table) {
            $table->dropColumn('company_id');
        });
    }
}
