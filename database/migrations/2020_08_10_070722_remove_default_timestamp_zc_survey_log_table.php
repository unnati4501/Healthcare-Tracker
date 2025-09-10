<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RemoveDefaultTimestampZcSurveyLogTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('zc_survey_log', function (Blueprint $table) {
            $table->dateTime('roll_out_date')->default(null)->change()->comment("roll out date of survey");
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('zc_survey_log', function (Blueprint $table) {
            $table->dateTime('roll_out_date')->useCurrent()->change()->comment("roll out date of survey");
        });
    }
}
