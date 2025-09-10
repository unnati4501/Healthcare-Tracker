<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUserPointLogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_point_logs', function (Blueprint $table) {
            $table->bigIncrements('id')->comment("primary key of the current table");
            $table->unsignedBigInteger('user_id')->nullable()->comment("refers to users table");
            $table->morphs('model');
            $table->integer('point')->default(0)->comment('Points earned for the action');
            $table->timestamp('created_at')->useCurrent()->comment("date and time when record is created");
            $table->timestamp('updated_at')->useCurrent()->comment("date and time when record is created");

            // setting up cardinality with `companies` tabel using `user_id` field
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
        Schema::dropIfExists('user_point_logs');
        Schema::enableForeignKeyConstraints();
    }
}
