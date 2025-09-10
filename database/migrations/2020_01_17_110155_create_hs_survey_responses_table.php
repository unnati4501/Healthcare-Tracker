<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateHsSurveyResponsesTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('hs_survey_responses', function (Blueprint $table) {
            $table->bigIncrements('id')->comment("primary key of the current table");
            $table->unsignedBigInteger('survey_id')->index('survey_id')->comment("refers to survey table");
            $table->unsignedBigInteger('question_id')->index('question_id')->comment("refers to question table");
            $table->unsignedBigInteger('sub_category_id')->index('sub_category_id')->comment("refers to sub_categories table");
            $table->unsignedBigInteger('category_id')->index('category_id')->comment("refers to categories table");
            $table->text('answer_value', 65535)->comment('ex: 1, 0 , yes, no , text answer of a question');
            $table->float('score', 10, 0)->comment('score of answer');
            $table->timestamp('created_at')->useCurrent()->comment("date and time when record is created");
            $table->timestamp('updated_at')->useCurrent()->comment("date and time when record is updated");
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('hs_survey_responses');
    }
}
