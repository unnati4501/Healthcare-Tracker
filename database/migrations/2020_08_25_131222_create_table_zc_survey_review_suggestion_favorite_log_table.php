<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTableZcSurveyReviewSuggestionFavoriteLogTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('zc_survey_review_suggestion_log', function (Blueprint $table) {
            $table->bigIncrements('id')->comment("primary key of the current table");
            $table->unsignedBigInteger('company_id')->index('company_id')->comment("refers to companies table");
            // $table->unsignedBigInteger('user_id')->index('user_id')->comment("refers to users table");
            $table->unsignedBigInteger('suggestion_id')->index('suggestion_id')->comment("refers to zc_survey_review_suggestion table");
            $table->boolean('is_favorite')->default(true);
            $table->timestamp('created_at')->useCurrent()->comment("date and time when record is created");
            $table->timestamp('updated_at')->useCurrent()->comment("date and time when record is updated");

            // foreign keys
            $table->foreign('company_id')->references('id')->on('companies')->onUpdate('CASCADE')->onDelete('CASCADE');
            // $table->foreign('user_id')->references('id')->on('users')->onUpdate('CASCADE')->onDelete('CASCADE');
            $table->foreign('suggestion_id')->references('id')->on('zc_survey_review_suggestion')->onUpdate('CASCADE')->onDelete('CASCADE');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists("zc_survey_review_suggestion_log");
    }
}
