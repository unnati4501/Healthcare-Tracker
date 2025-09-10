<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUserGoalTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_goal', function (Blueprint $table) {
            $table->bigIncrements('id')->comment("Primary key of the current table");

            $table->unsignedBigInteger('user_id')->comment("refers to users table");

            $table->double('weight')->nullable()->comment("user weight goal in KG");
            $table->unsignedInteger('steps')->nullable()->comment("user steps goal per day");
            $table->unsignedInteger('calories')->nullable()->comment("user calories goal per day in Kcal");

            $table->timestamp('created_at')->useCurrent()->comment("date and time when record is created");
            $table->timestamp('updated_at')->useCurrent()->comment("date and time when record is updated");

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
        Schema::dropIfExists('user_goal');
        Schema::enableForeignKeyConstraints();
    }
}
