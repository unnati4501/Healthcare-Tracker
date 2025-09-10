<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateZcQuestionsOptionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('zc_questions_options', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('question_id')->unsigned()->index('question_id')->comment('id from question table');
            $table->float('score', 10, 0)->comment('score of option');
            $table->string('choice', 191)->comment('actual value of answer');
            $table->timestamp('created_at')->useCurrent()->comment("date and time when record is created");
            $table->timestamp('updated_at')->useCurrent()->comment("date and time when record is updated");

            $table->foreign('question_id')
                ->references('id')
                ->on('zc_questions')
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
        Schema::dropIfExists('zc_questions_options');
    }
}
