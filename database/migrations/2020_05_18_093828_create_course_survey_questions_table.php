<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCourseSurveyQuestionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('course_survey_questions', function (Blueprint $table) {
            $table->bigIncrements('id')->comment("primary key of the current table");
            $table->unsignedBigInteger('survey_id')->index('survey_id')->comment("refers to course_survey table");
            $table->text('title', 65535)->comment('title of question');
            $table->enum('type', ['single_choice', 'multiple_choice']);
            $table->boolean('status')->default(true)->comment('1 => Active, 0 => Inactive status of question');
            $table->timestamp('created_at')->useCurrent()->comment("date and time when record is created");
            $table->timestamp('updated_at')->useCurrent()->comment("date and time when record is updated");

            $table->foreign('survey_id')
                ->references('id')
                ->on('course_survey')
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
        Schema::dropIfExists('course_survey_questions');
    }
}
