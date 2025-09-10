<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSurveyColumnsUserCourseTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('user_course', function (Blueprint $table) {
            $table->boolean('pre_survey_completed', false)->after('completed_on')->comment("flag to check user completed pre survey or not")->after('user_id');
            $table->timestamp('pre_survey_completed_on')->nullable()->after('pre_survey_completed')->comment("datetime when user completed the pre survey");
            $table->boolean('post_survey_completed', false)->after('pre_survey_completed_on')->comment("flag to check user completed post survey or not")->after('user_id');
            $table->timestamp('post_survey_completed_on')->nullable()->after('post_survey_completed')->comment("datetime when user completed the post survey");
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('user_course', function (Blueprint $table) {
            $table->dropColumn('pre_survey_completed');
            $table->dropColumn('pre_survey_completed_on');
            $table->dropColumn('post_survey_completed');
            $table->dropColumn('post_survey_completed_on');
        });
    }
}
