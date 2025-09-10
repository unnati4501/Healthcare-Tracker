<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSurveyToAllColumnToZcSurveySettings extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('zc_survey_settings', function (Blueprint $table) {
            $table->boolean('survey_to_all')->default(true)
                ->comment('1 => Send survey to all users; 0 => Send survey to selected users only')
                ->after('is_premium');
            $table->json('team_ids')->nullable()
                ->comment('comma seperated teams id of selected users for survey')
                ->after('survey_to_all');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::enableForeignKeyConstraints();
        Schema::table('zc_survey_settings', function (Blueprint $table) {
            $table->dropColumn('survey_to_all');
            $table->dropColumn('team_ids');
        });
        Schema::disableForeignKeyConstraints();
    }
}
