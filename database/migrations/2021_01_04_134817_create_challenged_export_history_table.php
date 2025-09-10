<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateChallengedExportHistoryTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('challenge_export_history', function (Blueprint $table) {
            $table->bigIncrements('id')->comment("primary key of the current table");
            $table->unsignedBigInteger('challenge_id')->nullable()->comment("refers to challenges table");
            $table->unsignedBigInteger('user_id')->comment("refers to users table");
            $table->enum('status', [1, 2, 3])->comment("1 => Inprocess, 2 => Completed, 3 => Failed");
            $table->timestamp('process_started_at')->nullable()->comment("date and time when export process is started");
            $table->timestamp('process_completed_at')->nullable()->comment("date and time when export process is completed");
            $table->timestamp('created_at')->useCurrent()->comment("date and time when record is created");
            $table->timestamp('updated_at')->useCurrent()->comment("date and time when record is updated");

            $table->foreign('challenge_id')
                ->references('id')->on('challenges')
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
        Schema::dropIfExists('challenge_export_history');
        Schema::enableForeignKeyConstraints();
    }
}
