<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddScoreTypeColumnToCourseSurveyQuestionOptions extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('course_survey_question_options', function (Blueprint $table) {
            $table->integer('score')->default(0)->comment('Score of the question')->after('choice');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('course_survey_question_options', function (Blueprint $table) {
            $table->dropColumn('score');
        });
    }
}
