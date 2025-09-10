<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSurveySubmissionDateColumnSurveyUserLogTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('zc_survey_user_log', function (Blueprint $table) {
            $table
                ->timestamp('survey_submitted_at')
                ->nullable()
                ->comment('User survey submission date and time')
                ->after('survey_log_id');
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
            $table->dropColumn('survey_submitted_at');
        });
    }
}
