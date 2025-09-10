<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddCompanyIdChallengeWiseTeamPointsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('challenge_wise_team_ponits', function (Blueprint $table) {
            $table->unsignedBigInteger('company_id')->after('challenge_id')->nullable()->index('company_id')->comment("refers to companies table");
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('challenge_wise_team_ponits', function (Blueprint $table) {
            $table->dropColumn('company_id');
        });
    }
}
