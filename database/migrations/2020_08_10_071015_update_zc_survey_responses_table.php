<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateZcSurveyResponsesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('zc_survey_responses', function (Blueprint $table) {
            $table->dropColumn('survey_id');
            $table->unsignedBigInteger('survey_log_id')->after('department_id')->index('survey_log_id')->comment("refers to zc_survey_log table");

            $table->foreign('user_id')
                ->references('id')
                ->on('zc_survey_log')
                ->onUpdate('CASCADE')
                ->onDelete('CASCADE');

            $table->foreign('company_id')
                ->references('id')
                ->on('zc_survey_log')
                ->onUpdate('CASCADE')
                ->onDelete('CASCADE');

            $table->foreign('department_id')
                ->references('id')
                ->on('zc_survey_log')
                ->onUpdate('CASCADE')
                ->onDelete('CASCADE');

            $table->foreign('survey_log_id')
                ->references('id')
                ->on('zc_survey_log')
                ->onUpdate('CASCADE')
                ->onDelete('CASCADE');

            $table->foreign('category_id')
                ->references('id')
                ->on('zc_survey_log')
                ->onUpdate('CASCADE')
                ->onDelete('CASCADE');

            $table->foreign('sub_category_id')
                ->references('id')
                ->on('zc_survey_log')
                ->onUpdate('CASCADE')
                ->onDelete('CASCADE');

            $table->foreign('question_id')
                ->references('id')
                ->on('zc_survey_log')
                ->onUpdate('CASCADE')
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
        Schema::table('zc_survey_responses', function (Blueprint $table) {
            $table->unsignedBigInteger('survey_id')->index('survey_id')->comment("refers to survey table");
            $table->dropForeign('zc_survey_responses_user_id_foreign');
            $table->dropForeign('zc_survey_responses_company_id_foreign');
            $table->dropForeign('zc_survey_responses_department_id_foreign');
            $table->dropForeign('zc_survey_responses_survey_log_id_foreign');
            $table->dropForeign('zc_survey_responses_category_id_foreign');
            $table->dropForeign('zc_survey_responses_sub_category_id_foreign');
            $table->dropForeign('zc_survey_responses_question_id_foreign');
            $table->dropColumn('survey_log_id');
        });
    }
}
