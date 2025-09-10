<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUserNpsSurveyLogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_nps_survey_logs', function (Blueprint $table) {
            $table->bigIncrements('id')->comment("primary key of current table");

            $table->unsignedBigInteger('user_id')->comment("refers to users table");
            
            $table->dateTime('survey_sent_on')->comment('date time when last time survey sent to the user for survey feedback');
            $table->integer('rating')->default(0)->comment('app ratings between 1 to 10 given by user');
            $table->string('feedback')->nullable()->comment('app feedback given by user');
            $table->dateTime('survey_received_on')->comment('date time when user given the app feedback as a survey');

            $table->timestamp('created_at')->useCurrent()->comment("date and time when record is created");
            $table->timestamp('updated_at')->useCurrent()->comment("date and time when record is updated");

            $table->foreign('user_id')
                ->references('id')->on('users')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('user_nps_survey_logs');
        Schema::enableForeignKeyConstraints();
    }
}
