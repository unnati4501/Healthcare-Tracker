<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateHsSurveyTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('hs_survey', function (Blueprint $table) {
            $table->bigIncrements('id')->comment("primary key of the current table");
            $table->unsignedBigInteger('company_id')->index('company_id')->comment("refers to company table");
            $table->unsignedBigInteger('department_id')->index('department_id')->comment("refers to department table");
            $table->unsignedBigInteger('team_id')->index('team_id')->comment("refers to team table");
            $table->unsignedBigInteger('user_id')->index('user_id')->comment("refers to users table");
            $table->text('title', 65535)->comment('title of survey');
            $table->dateTime('rolled_out_to_user')->nullable()->comment('survey roll out to user time');
            $table->dateTime('physical_survey_complete_time')->nullable()->comment('physical survey complete time');
            $table->dateTime('physcological_survey_complete_time')->nullable()->comment('physcological survey complete time');
            $table->float('physical_survey_score', 10, 0)->nullable()->comment('physical survey score of user');
            $table->float('physcological_survey_score', 10, 0)->nullable()->comment('physcological survey score of user');
            $table->dateTime('survey_complete_time')->nullable()->comment('survey complete time');
            $table->timestamp('created_at')->useCurrent()->comment("date and time when record is created");
            $table->timestamp('updated_at')->useCurrent()->comment("date and time when record is updated");
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('hs_survey');
    }
}
