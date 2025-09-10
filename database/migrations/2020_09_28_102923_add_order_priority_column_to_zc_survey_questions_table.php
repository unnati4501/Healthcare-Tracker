<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddOrderPriorityColumnToZcSurveyQuestionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('zc_survey_questions')) {
            Schema::table('zc_survey_questions', function (Blueprint $table) {
                $table->integer('order_priority')->default(0)->after('question_type_id')->index('order_priority')->comment('Order of the question');
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (Schema::hasTable('zc_survey_questions')) {
            Schema::table('zc_survey_questions', function (Blueprint $table) {
                $table->dropColumn('order_priority');
            });
        }
    }
}
