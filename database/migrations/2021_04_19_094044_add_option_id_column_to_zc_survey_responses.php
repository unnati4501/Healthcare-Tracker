<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddOptionIdColumnToZcSurveyResponses extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('zc_survey_responses', function (Blueprint $table) {
            $table->unsignedBigInteger('option_id')->index('option_id')
                ->nullable()->comment("refers to zc_questions_options table")
                ->after('question_id');
            $table->foreign('option_id')->references('id')->on('zc_questions_options')->onDelete('CASCADE');
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
            Schema::disableForeignKeyConstraints();
            $table->dropForeign('zc_survey_responses_option_id_foreign');
            $table->dropColumn('option_id');
            Schema::enableForeignKeyConstraints();
        });
    }
}
