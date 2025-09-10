<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUserStepTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_step', function (Blueprint $table) {
            $table->bigIncrements('id')->comment("primary key of the current table");

            $table->unsignedBigInteger('user_id')->comment("refers to users table");
            
            $table->string('tracker', 255)->comment('tracker shortname for synced data');
            $table->bigInteger('steps')->comment('steps synced - count');
            $table->bigInteger('distance')->comment('distance synced - meter');
            $table->bigInteger('calories')->comment('calories synced - kcal');
            $table->dateTime('log_date')->comment('data synced date and time');
            $table->string('route_url')->nullable()->comment('image url of route covered by activity');
            
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
        Schema::dropIfExists('user_step');
        Schema::enableForeignKeyConstraints();
    }
}
