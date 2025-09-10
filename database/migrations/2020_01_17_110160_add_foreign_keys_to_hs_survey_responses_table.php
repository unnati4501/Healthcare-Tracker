<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddForeignKeysToHsSurveyResponsesTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('hs_survey_responses', function (Blueprint $table) {
            $table->foreign('survey_id')->references('id')->on('hs_survey')->onUpdate('CASCADE')->onDelete('CASCADE');
            $table->foreign('question_id')->references('id')->on('hs_questions')->onUpdate('CASCADE')->onDelete('CASCADE');
            $table->foreign('sub_category_id')->references('id')->on('hs_sub_categories')->onUpdate('CASCADE')->onDelete('CASCADE');
            $table->foreign('category_id')->references('id')->on('hs_categories')->onUpdate('CASCADE')->onDelete('CASCADE');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('hs_survey_responses', function (Blueprint $table) {
            $table->dropForeign('hs_survey_responses_survey_id_foreign');
            $table->dropForeign('hs_survey_responses_question_id_foreign');
            $table->dropForeign('hs_survey_responses_sub_category_id_foreign');
            $table->dropForeign('hs_survey_responses_category_id_foreign');
        });
    }
}
