<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddMaxScoreColumnToZcSurveyResponses extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('zc_survey_responses', function (Blueprint $table) {
            \DB::statement("ALTER TABLE `zc_survey_responses` CHANGE `score` `score` DOUBLE NULL DEFAULT NULL COMMENT 'score of answer';");
            $table->double('max_score')->nullable()->default(null)->comment('Max score of the question optinos')->after('score');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('zc_survey_responses', function (Blueprint $table) {
            \DB::statement("ALTER TABLE `zc_survey_responses` CHANGE `score` `score` DOUBLE NULL DEFAULT '0' COMMENT 'score of answer';");
            $table->dropColumn('max_score');
        });
    }
}
