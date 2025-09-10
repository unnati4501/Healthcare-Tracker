<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateHealthCoachUserTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('health_coach_user', function (Blueprint $table) {
            $table->bigIncrements('id')->comment("primary key of the current table");
            $table->unsignedBigInteger('user_id')->nullable()->comment("refers to user table");
            $table->boolean('is_profile')->default(false)->comment("WC user profile should updated or not");
            $table->boolean('is_authenticate')->default(false)->comment("this is fields is use for WC have set calendar or not");
            $table->boolean('is_availability')->default(false)->comment("this is fields is use for WC have set availability or not");
            $table->boolean('is_cronofy')->default(false)->comment("WC user set his whole details or not.");
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
        Schema::dropIfExists('health_coach_user');
    }
}
