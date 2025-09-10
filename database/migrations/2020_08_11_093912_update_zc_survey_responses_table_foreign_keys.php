<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateZcSurveyResponsesTableForeignKeys extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // disable foreign key checks!
        Schema::disableForeignKeyConstraints();

        Schema::table('zc_survey_responses', function (Blueprint $table) {
            $table->dropForeign('zc_survey_responses_user_id_foreign');
            $table->foreign('user_id')->references('id')->on('users')->onUpdate('CASCADE')->onDelete('CASCADE');

            $table->dropForeign('zc_survey_responses_company_id_foreign');
            $table->foreign('company_id')->references('id')->on('companies')->onUpdate('CASCADE')->onDelete('CASCADE');

            $table->dropForeign('zc_survey_responses_department_id_foreign');
            $table->foreign('department_id')->references('id')->on('departments')->onUpdate('CASCADE')->onDelete('CASCADE');

            $table->dropForeign('zc_survey_responses_category_id_foreign');
            $table->foreign('category_id')->references('id')->on('zc_categories')->onUpdate('CASCADE')->onDelete('CASCADE');

            $table->dropForeign('zc_survey_responses_sub_category_id_foreign');
            $table->foreign('sub_category_id')->references('id')->on('zc_sub_categories')->onUpdate('CASCADE')->onDelete('CASCADE');

            $table->dropForeign('zc_survey_responses_question_id_foreign');
            $table->foreign('question_id')->references('id')->on('zc_questions')->onUpdate('CASCADE')->onDelete('CASCADE');

            $table->text('answer_value', 65535)->comment('text answer of a question')->nullable()->change();
            $table->float('score', 10, 0)->comment('score of answer')->default(0)->change();
        });

        // Enable foreign key checks!
        Schema::enableForeignKeyConstraints();
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('zc_survey_responses', function (Blueprint $table) {
            $table->text('answer_value', 65535)->comment('text answer of a question')->change();
            $table->float('score', 10, 0)->comment('score of answer')->change();
        });
    }
}
