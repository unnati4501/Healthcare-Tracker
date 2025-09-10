<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMasterclassCsatLogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('masterclass_csat_user_logs', function (Blueprint $table) {
            $table->bigIncrements('id')->comment("primary key of the current table");
            $table->unsignedBigInteger('user_id')->nullable()->comment("refers to users table");
            $table->unsignedBigInteger('company_id')->nullable()->comment("refers to companies table");
            $table->unsignedBigInteger('course_id')->comment("refers to courses table");
            $table
                ->enum('feedback_type', ['very_unhappy', 'unhappy', 'neutral', 'happy', 'very_happy'])
                ->default('happy')
                ->comment("feedback type(Very Unhappy, Unhappy, Neutral, Happy, Very Happy)")
                ->index();
            $table->string('feedback', 1000)->nullable()->comment('feedback given by user');
            $table->timestamp('created_at')->useCurrent()->comment("date and time when record is created");
            $table->timestamp('updated_at')->useCurrent()->comment("date and time when record is updated");

            // Setting foreign key constraints
            $table->foreign('company_id')->references('id')->on('companies')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('course_id')->references('id')->on('courses')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::enableForeignKeyConstraints();
        Schema::dropIfExists('masterclass_csat_user_logs');
        Schema::disableForeignKeyConstraints();
    }
}
