<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCompanyIdColumnToCourseSurveyQuestionAnswers extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('course_survey_question_answers', function (Blueprint $table) {
            $table->unsignedBigInteger('company_id')->index('company_id')->nullable()->after('user_id')->comment("refers to companies table");

            // Add foreign key constraint to company id column
            $table->foreign('company_id')->references('id')->on('companies')->onDelete('CASCADE');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('course_survey_question_answers', function (Blueprint $table) {
            $table->dropForeign(['company_id']);
            $table->dropColumn('company_id');
        });
    }
}
