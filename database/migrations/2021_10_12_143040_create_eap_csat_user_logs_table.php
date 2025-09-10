<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEapCsatUserLogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('eap_csat_user_logs', function (Blueprint $table) {
            $table->bigIncrements('id')->comment("primary key of the current table");
            $table->unsignedBigInteger('user_id')->nullable()->comment("refers to users table");
            $table->unsignedBigInteger('company_id')->nullable()->comment("refers to companies table");
            $table->unsignedBigInteger('eap_calendy_id')->comment("refers to eap_calendly table");
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
            $table->foreign('eap_calendy_id')->references('id')->on('eap_calendly')->onDelete('cascade');
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
        Schema::dropIfExists('eap_csat_user_logs');
        Schema::disableForeignKeyConstraints();
    }
}
