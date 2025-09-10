<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSurveyFlagColumnInHsSurveyTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('hs_survey', function (Blueprint $table) {
            $table->boolean('physical_survey_started')->default(0)->after('rolled_out_to_user')->comment('Flag to check physical survey started or not');
            $table->boolean('physcological_survey_started')->default(0)->after('physical_survey_started')->comment('Flag to check physcological survey started or not');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('hs_survey', function (Blueprint $table) {
            $table->dropColumn('physical_survey_started');
            $table->dropColumn('physcological_survey_started');
        });
    }
}
