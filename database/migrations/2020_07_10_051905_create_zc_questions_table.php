<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateZcQuestionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('zc_questions', function (Blueprint $table) {
            $table->bigIncrements('id')->comment("primary key of the current table");
            $table->unsignedBigInteger('category_id')->unsigned()->index('category_id')->comment('id from survey category table');
            $table->unsignedBigInteger('sub_category_id')->unsigned()->index('sub_category_id')->comment('id from survey subcategory table');
            $table->unsignedBigInteger('question_type_id')->unsigned()->index('question_type_id')->comment('id from survey question_type table');
            $table->text('title', 65535)->comment('title of question');
            $table->boolean('status')->default(1);
            $table->timestamp('created_at')->useCurrent()->comment("date and time when record is created");
            $table->timestamp('updated_at')->useCurrent()->comment("date and time when record is updated");

            $table->foreign('category_id')
                ->references('id')
                ->on('zc_categories')
                ->onUpdate('CASCADE')
                ->onDelete('CASCADE');

            $table->foreign('sub_category_id')
                ->references('id')
                ->on('zc_sub_categories')
                ->onUpdate('CASCADE')
                ->onDelete('CASCADE');

            $table->foreign('question_type_id')
                ->references('id')
                ->on('zc_question_types')
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
        Schema::dropIfExists('zc_questions');
    }
}
