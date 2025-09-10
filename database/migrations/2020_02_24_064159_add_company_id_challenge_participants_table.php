<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCompanyIdChallengeParticipantsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('challenge_participants', function (Blueprint $table) {
            $table->unsignedBigInteger('company_id')->after('team_id')->nullable()->comment("refers to companies table");
            $table->foreign('company_id')
                ->references('id')
                ->on('companies')
                ->onDelete('CASCADE');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('challenge_participants', function (Blueprint $table) {
            $table->dropForeign('challenge_participants_company_id_foreign');
            $table->dropColumn('company_id');
        });
    }
}
