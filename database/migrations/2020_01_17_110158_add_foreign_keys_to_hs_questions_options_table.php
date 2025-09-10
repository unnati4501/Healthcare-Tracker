<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddForeignKeysToHsQuestionsOptionsTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('hs_questions_options', function (Blueprint $table) {
            $table->foreign('question_id')->references('id')->on('hs_questions')->onUpdate('CASCADE')->onDelete('CASCADE');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('hs_questions_options', function (Blueprint $table) {
            $table->dropForeign('hs_questions_options_question_id_foreign');
        });
    }
}
