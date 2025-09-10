<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddForeignKeysToHsQuestionsTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('hs_questions', function (Blueprint $table) {
            $table->foreign('category_id')->references('id')->on('hs_categories')->onUpdate('CASCADE')->onDelete('CASCADE');
            $table->foreign('sub_category_id')->references('id')->on('hs_sub_categories')->onUpdate('CASCADE')->onDelete('CASCADE');
            $table->foreign('question_type_id')->references('id')->on('hs_question_type')->onUpdate('CASCADE')->onDelete('CASCADE');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('hs_questions', function (Blueprint $table) {
            $table->dropForeign('hs_questions_category_id_foreign');
            $table->dropForeign('hs_questions_sub_category_id_foreign');
            $table->dropForeign('hs_questions_question_type_id_foreign');
        });
    }
}
