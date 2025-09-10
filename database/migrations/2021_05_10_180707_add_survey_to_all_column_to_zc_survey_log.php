<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSurveyToAllColumnToZcSurveyLog extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('zc_survey_log', function (Blueprint $table) {
            $table->boolean('survey_to_all')->default(true)->comment('survey_to_all for column status of the company while rolling out the survey')->after('expire_date');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('zc_survey_log', function (Blueprint $table) {
            $table->dropColumn('survey_to_all');
        });
    }
}
