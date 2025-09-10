<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateZcSurveyReviewSuggestionTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('zc_survey_review_suggestion', function (Blueprint $table) {
            $table->bigIncrements('id')->comment("primary key of the current table");
            $table->unsignedBigInteger('user_id')->index('user_id')->comment("refers to users table");
            $table->unsignedBigInteger('company_id')->index('company_id')->comment("refers to company table");
            $table->unsignedBigInteger('department_id')->index('department_id')->comment("refers to department table");
            $table->unsignedBigInteger('survey_log_id')->index('survey_log_id')->comment("refers to survey table");
            $table->text('comment', 65535)->comment('review or suggestion');
            $table->timestamp('created_at')->useCurrent()->comment("date and time when record is created");
            $table->timestamp('updated_at')->useCurrent()->comment("date and time when record is updated");

            // foreign keys
            $table->foreign('user_id')->references('id')->on('users')->onUpdate('CASCADE')->onDelete('CASCADE');
            $table->foreign('company_id')->references('id')->on('companies')->onUpdate('CASCADE')->onDelete('CASCADE');
            $table->foreign('department_id')->references('id')->on('departments')->onUpdate('CASCADE')->onDelete('CASCADE');
            $table->foreign('survey_log_id')->references('id')->on('zc_survey_log')->onUpdate('CASCADE')->onDelete('CASCADE');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('zc_survey_review_suggestion');
    }
}
