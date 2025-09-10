<?php

use App\Http\Traits\DisableForeignKeys;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCourseIdColumnCourseSurveyQuesAnsTable extends Migration
{
    use DisableForeignKeys;

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $this->disableForeignKeys();
        Schema::table('course_survey_question_answers', function (Blueprint $table) {
            $table->unsignedBigInteger('course_id')->after('user_id')->comment("refers to course table");

            $table->foreign('course_id')
                ->references('id')
                ->on('courses')
                ->onUpdate('CASCADE')
                ->onDelete('CASCADE');
        });

        // Enable foreign key checks!
        $this->enableForeignKeys();
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $this->disableForeignKeys();

        Schema::table('course_survey_question_answers', function (Blueprint $table) {
            $table->dropForeign('course_survey_question_answers_course_id_foreign');
            $table->dropColumn('course_id');
        });

        // Enable foreign key checks!
        $this->enableForeignKeys();
    }
}
