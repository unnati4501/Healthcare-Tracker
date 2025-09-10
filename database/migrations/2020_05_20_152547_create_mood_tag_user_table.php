<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMoodTagUserTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('mood_tag_user', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('user_id')->comment("refers to users table");
            $table->unsignedBigInteger('company_id')->nullable()->comment("refers to company table");
            $table->unsignedBigInteger('mood_id')->comment("refers to moods table");
            $table->unsignedBigInteger('tag_id')->comment("refers to tags table");
            $table->dateTime('date')->nullable()->comment('daily date of mood');
            $table->timestamp('created_at')->useCurrent()->comment("date and time when record is created");
            $table->timestamp('updated_at')->useCurrent()->comment("date and time when record is updated");

            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->onUpdate('CASCADE')
                ->onDelete('CASCADE');

            $table->foreign('company_id')
                ->references('id')
                ->on('companies')
                ->onUpdate('CASCADE')
                ->onDelete('CASCADE');

            $table->foreign('mood_id')
                ->references('id')
                ->on('moods')
                ->onUpdate('CASCADE')
                ->onDelete('CASCADE');

            $table->foreign('tag_id')
                ->references('id')
                ->on('mood_tags')
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
        Schema::dropIfExists('mood_tag_user');
    }
}
