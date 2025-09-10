<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUserCoachLogTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_coach_log', function (Blueprint $table) {
            $table->bigIncrements('id')->comment("primary key of the current table");

            $table->unsignedBigInteger('coach_id')->comment("refers to users table who created the content");
            $table->unsignedBigInteger('user_id')->comment("refers to users table");
            
            $table->boolean('followed')->default(false)->comment('true, if user is following the coach');
            $table->boolean('liked')->default(false)->comment('true, if user is following the coach');
            $table->integer('ratings')->default(0)->comment('coach rating given by user');
            $table->string('review', 255)->nullable()->comment('coach review given by user');
            
            $table->timestamp('created_at')->useCurrent()->comment("date and time when record is created");
            $table->timestamp('updated_at')->useCurrent()->comment("date and time when record is updated");

            $table->foreign('coach_id')
                ->references('id')->on('users')
                ->onDelete('cascade');
            $table->foreign('user_id')
                ->references('id')->on('users')
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
        Schema::dropIfExists('user_coach_log');
        Schema::enableForeignKeyConstraints();
    }
}
