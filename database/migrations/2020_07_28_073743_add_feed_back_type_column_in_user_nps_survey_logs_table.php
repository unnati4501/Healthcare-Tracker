<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddFeedBackTypeColumnInUserNpsSurveyLogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('user_nps_survey_logs', function (Blueprint $table) {
            $table->dropColumn('rating');
            $table->enum('feedback_type', ['very_happy', 'happy', 'neutral', 'unhappy', 'very_unhappy'])->nullable()->after('feedback')->comment("feedback type (Very Happy, Happy, Neutral, Unhappy, Very Unhappy ) ");
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
            $table->integer('rating')->default(0)->comment('app ratings between 1 to 10 given by user');
            $table->dropColumn('feedback_type');
        });
    }
}
