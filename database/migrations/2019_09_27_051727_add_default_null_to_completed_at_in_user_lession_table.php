<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddDefaultNullToCompletedAtInUserLessionTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('user_lession', function (Blueprint $table) {
            $table->dateTime('completed_at')->default(null)->comment("date and time when course is completed")->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('user_lession', function (Blueprint $table) {
            //
        });
    }
}
