<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateZcSurveySettingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('zc_survey_settings', function (Blueprint $table) {
            $table->bigIncrements('id')->comment("primary key of the current table");
            $table->unsignedBigInteger('company_id')->index('company_id')->comment("refers to company table");
            $table->unsignedBigInteger('survey_id')->index('survey_id')->comment("refers to survey table");
            $table->addColumn('tinyInteger', 'survey_frequency', ['lenght' => 4]);
            $table->string('survey_roll_out_day', 191)->comment('rolloutday for survey');
            $table->time('survey_roll_out_time')->comment('rollouttime for survey');
            $table->boolean('is_premium')->default(0);
            $table->timestamp('created_at')->useCurrent()->comment("date and time when record is created");
            $table->timestamp('updated_at')->useCurrent()->comment("date and time when record is updated");

            $table->foreign('company_id')
                ->references('id')
                ->on('companies')
                ->onUpdate('CASCADE')
                ->onDelete('CASCADE');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('zc_survey_settings');
    }
}
