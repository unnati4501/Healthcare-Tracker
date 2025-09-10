<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUsersStepsAuthenticatorAvgTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users_steps_authenticator_avg', function (Blueprint $table) {
            $table->bigIncrements('id')->comment("primary key of the current table");
            $table->unsignedBigInteger('user_id')->comment("refers to users table");
            $table->bigInteger('steps_total')->comment('steps total synced - count');
            $table->float('steps_avg', 10, 0)->comment('last based on days find avg');
            $table->bigInteger('days')->comment('how may day to calculate avg');
            $table->dateTime('log_date')->comment('for which date calculate and find avg');
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
        Schema::dropIfExists('users_steps_authenticator_avg');
        Schema::enableForeignKeyConstraints();
    }
}
