<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateHsQuestionsTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('hs_questions', function (Blueprint $table) {
            $table->bigIncrements('id')->comment("primary key of the current table");
            $table->unsignedBigInteger('category_id')->unsigned()->index('category_id')->comment('id from categories table');
            $table->unsignedBigInteger('sub_category_id')->nullable()->index('sub_category_id')->comment('id from sub_categories table');
            $table->unsignedBigInteger('question_type_id')->unsigned()->index('question_type_id')->comment('id from question_type table');
            $table->text('title', 65535)->comment('title of question');
            $table->integer('image')->unsigned()->nullable()->comment('link to assets image');
            $table->float('max_score', 10, 0)->comment('max score of question');
            $table->boolean('status')->default(1)->comment('1 => Active, 0 => Inactive status of question');
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
        Schema::drop('hs_questions');
    }
}
