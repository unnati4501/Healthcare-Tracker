<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddPortalFieldInUserNpsSurveyLogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('user_nps_survey_logs', function (Blueprint $table) {
            $table->enum('is_portal', ['0', '1'])->default(0)->comment('0 => false, 1 => true')->after('survey_received_on');
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
            $table->dropColumn('is_portal');
        });
    }
}
