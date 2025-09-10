<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateZcSurveyTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('zc_survey', function (Blueprint $table) {
            $table->bigIncrements('id')->comment("primary key of the current table");
            $table->string('title')->comment('title of survey');
            $table->text('description')->nullable()->comment("description of survey");
            $table->enum('status', ['Draft', 'Published', 'Assigned'])->default('Draft')->comment('status of survey');
            $table->boolean('is_premium')->default(0)->comment('Is premium if includes questions from premium subcategories');
            $table->timestamp('created_at')->useCurrent()->comment("date and time when record is created");
            $table->timestamp('updated_at')->useCurrent()->comment("date and time when record is updated");
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('zc_survey');
    }
}
