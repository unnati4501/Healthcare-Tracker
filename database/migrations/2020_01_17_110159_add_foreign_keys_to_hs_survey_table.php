<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddForeignKeysToHsSurveyTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('hs_survey', function (Blueprint $table) {
            $table->foreign('company_id')->references('id')->on('companies')->onUpdate('CASCADE')->onDelete('CASCADE');
            $table->foreign('department_id')->references('id')->on('departments')->onUpdate('CASCADE')->onDelete('CASCADE');
            $table->foreign('team_id')->references('id')->on('teams')->onUpdate('CASCADE')->onDelete('CASCADE');
            $table->foreign('user_id')->references('id')->on('users')->onUpdate('CASCADE')->onDelete('CASCADE');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('hs_survey', function (Blueprint $table) {
            $table->dropForeign('hs_survey_company_id_foreign');
            $table->dropForeign('hs_survey_department_id_foreign');
            $table->dropForeign('hs_survey_team_id_foreign');
            $table->dropForeign('hs_survey_user_id_foreign');
        });
    }
}
