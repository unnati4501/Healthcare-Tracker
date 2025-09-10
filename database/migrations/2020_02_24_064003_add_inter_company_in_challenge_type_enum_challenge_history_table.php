<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddInterCompanyInChallengeTypeEnumChallengeHistoryTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('challenge_history', function (Blueprint $table) {
            DB::statement("ALTER TABLE challenge_history MODIFY COLUMN challenge_type ENUM('individual', 'team', 'company_goal', 'inter_company')");
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('challenge_history', function (Blueprint $table) {
            DB::statement("ALTER TABLE challenge_history MODIFY COLUMN challenge_type ENUM('individual', 'team', 'company_goal')");
        });
    }
}
