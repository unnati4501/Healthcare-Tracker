<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCourseSurveyQuestionAnswersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('course_survey_question_answers', function (Blueprint $table) {
            $table->bigIncrements('id')->comment("primary key of the current table");
            $table->unsignedBigInteger('user_id')->index('user_id')->comment("refers to users table");
            $table->unsignedBigInteger('survey_id')->index('survey_id')->comment("refers to course_survey table");
            $table->unsignedBigInteger('question_id')->index('question_id')->comment("refers to course_survey_questions table");
            $table->unsignedBigInteger('question_option_id')->index('question_option_id')->comment("refers to course_survey_question_options table");
            $table->timestamp('created_at')->useCurrent()->comment("date and time when record is created");
            $table->timestamp('updated_at')->useCurrent()->comment("date and time when record is updated");

            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->onUpdate('CASCADE')
                ->onDelete('CASCADE');

            $table->foreign('survey_id')
                ->references('id')
                ->on('course_survey')
                ->onUpdate('CASCADE')
                ->onDelete('CASCADE');

            $table->foreign('question_id')
                ->references('id')
                ->on('course_survey_questions')
                ->onUpdate('CASCADE')
                ->onDelete('CASCADE');

            $table->foreign('question_option_id')
                ->references('id')
                ->on('course_survey_question_options')
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
        Schema::dropIfExists('course_survey_question_answers');
    }
}
