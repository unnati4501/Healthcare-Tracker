<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateFeedbackFieldInUserNpsSurveyLogsTable extends Migration
{
    public function __construct()
    {
        DB::getDoctrineSchemaManager()->getDatabasePlatform()->registerDoctrineTypeMapping('enum', 'string');
    }
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('user_nps_survey_logs', function (Blueprint $table) {
            $table->string('feedback', 1000)->nullable()->comment('app feedback given by user')->change();
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
            $table->string('feedback')->nullable()->comment('app feedback given by user')->change();
        });
    }
}
