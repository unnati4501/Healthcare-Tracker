<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCompanyWiseChallengeSettingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('company_wise_challenge_settings', function (Blueprint $table) {
            $table->bigIncrements('id')->comment("primary key of current table");
            
            $table->unsignedBigInteger('company_id')->comment("refers to companies table");

            $table->string('type', 255)->nullable()->comment('refers to target name');
            $table->integer('value')->nullable()->comment('refers to maximum number of points for target per day in challenge');
            $table->string('uom', 255)->nullable()->comment('refers to uom of target');

            $table->timestamp('created_at')->useCurrent()->comment("date and time when record is created");
            $table->timestamp('updated_at')->useCurrent()->comment("date and time when record is updated");

            $table->foreign('company_id')
                ->references('id')->on('companies')
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
        Schema::dropIfExists('company_wise_challenge_settings');
        Schema::enableForeignKeyConstraints();
    }
}
