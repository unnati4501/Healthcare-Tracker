<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddTypeColumnChallengeHistoryTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('challenge_history', function (Blueprint $table) {
            $table->enum('challenge_type', ['individual', 'team', 'company_goal'])->after('challenge_category_id')->default('individual');
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
            $table->dropColumn('challenge_type');
        });
    }
}
