<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddRetakeColumnToZcSurveyUserLogTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('zc_survey_user_log', function (Blueprint $table) {
            $table->unsignedBigInteger('retake')->default(0)->comment('Survey Retake Count - How many survey filled for perticular user.')->after('survey_submitted_at');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('zc_survey_user_log', function (Blueprint $table) {
            $table->dropColumn('retake');
        });
    }
}
