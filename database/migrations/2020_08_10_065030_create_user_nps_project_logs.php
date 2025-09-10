<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUserNpsProjectLogs extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_nps_project_logs', function (Blueprint $table) {
            $table->bigIncrements('id')->comment("primary key of the current table");
            $table->unsignedBigInteger('user_id')->nullable()->comment("refers to users table");
            $table->unsignedBigInteger('nps_project_id')->comment("refers to nps_project table");
            $table->enum('feedback_type', ['very_happy', 'happy', 'neutral', 'unhappy', 'very_unhappy'])->nullable()->comment("feedback type (Very Happy, Happy, Neutral, Unhappy, Very Unhappy ) ");
            $table->string('feedback')->nullable()->comment('project survey feedback given by user');
            $table->dateTime('survey_received_on')->nullable()->comment('date time when user given the app feedback as a survey');

            $table->timestamp('created_at')->useCurrent()->comment("date and time when record is created");
            $table->timestamp('updated_at')->useCurrent()->comment("date and time when record is updated");

            $table->foreign('user_id')
                ->references('id')->on('users')
                ->onDelete('cascade');
            $table->foreign('nps_project_id')
                ->references('id')->on('nps_project')
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
        Schema::dropIfExists('user_nps_project_logs');
        Schema::enableForeignKeyConstraints();
    }
}
