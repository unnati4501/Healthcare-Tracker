<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCompanyWiseAppSettingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('company_wise_app_settings', function (Blueprint $table) {
            $table->bigIncrements('id')->comment("primary key of current table");
            $table->unsignedBigInteger('company_id')->comment("refers to companies table");
            $table->string('key', 255)->comment("key/type for the field value");
            $table->string('value', 255)->nullable()->comment("value for the field");
            $table->string('type', 255)->nullable()->comment("type for the field like textbox, radio, file");
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
        Schema::dropIfExists('company_wise_app_settings');
    }
}
