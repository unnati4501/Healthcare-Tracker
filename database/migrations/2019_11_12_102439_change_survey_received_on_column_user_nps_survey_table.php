<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ChangeSurveyReceivedOnColumnUserNpsSurveyTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('user_nps_survey_logs', function (Blueprint $table) {
            $table->dateTime('survey_received_on')->nullable()->comment('date time when user give servey')->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('user_nps_survey_logs', function (Blueprint $table) {
            //
        });
    }
}
