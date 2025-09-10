<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateZcSurveyResponsesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('zc_survey_responses', function (Blueprint $table) {
            $table->bigIncrements('id')->comment("primary key of the current table");
            $table->unsignedBigInteger('user_id')->index('user_id')->comment("refers to users table");
            $table->unsignedBigInteger('company_id')->index('company_id')->comment("refers to company table");
            $table->unsignedBigInteger('department_id')->index('department_id')->comment("refers to department table");
            $table->unsignedBigInteger('survey_id')->index('survey_id')->comment("refers to survey table");
            $table->unsignedBigInteger('category_id')->index('category_id')->comment("refers to zccategories table");
            $table->unsignedBigInteger('sub_category_id')->index('sub_category_id')->comment("refers to zcsubcategories table");
            $table->unsignedBigInteger('question_id')->index('question_id')->comment("refers to zcquestions table");
            $table->text('answer_value', 65535)->comment('text answer of a question');
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
        Schema::dropIfExists('zc_survey_responses');
    }
}
