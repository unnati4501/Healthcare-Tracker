<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateZcSurveyLogTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('zc_survey_log', function (Blueprint $table) {
            $table->bigIncrements('id')->comment("primary key of the current table");
            $table->unsignedBigInteger('company_id')->index('company_id')->comment("refers to company table");
            $table->unsignedBigInteger('survey_id')->index('survey_id')->comment("refers to survey table");
            $table->dateTime('roll_out_date')->useCurrent()->comment("roll out date of survey");
            $table->time('roll_out_time')->comment('roll out time of survey');
            $table->dateTime('expire_date')->comment("expire date of survey");
            $table->timestamp('created_at')->useCurrent()->comment("date and time when record is created");
            $table->timestamp('updated_at')->useCurrent()->comment("date and time when record is updated");

            $table->foreign('company_id')
                ->references('id')
                ->on('companies')
                ->onUpdate('CASCADE')
                ->onDelete('CASCADE');

            $table->foreign('survey_id')
                ->references('id')
                ->on('zc_survey')
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
        Schema::dropIfExists('zc_survey_log');
    }
}
