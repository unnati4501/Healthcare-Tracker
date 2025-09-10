<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddInterCompanyInChallengeTypeEnumChallengeTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('challenges', function (Blueprint $table) {
            DB::statement("ALTER TABLE challenges MODIFY COLUMN challenge_type ENUM('individual', 'team', 'company_goal', 'inter_company')");
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('challenges', function (Blueprint $table) {
            DB::statement("ALTER TABLE challenges MODIFY COLUMN challenge_type ENUM('individual', 'team', 'company_goal')");
        });
    }
}
