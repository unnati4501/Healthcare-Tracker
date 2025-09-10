<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterDoubleTypeColumnInTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::statement('alter table user_weight modify weight DOUBLE(10,2) COMMENT "user weight in KG on current date"');

        DB::statement('alter table user_goal modify weight DOUBLE(10,2) COMMENT "user weight in KG on current date"');

        DB::statement('alter table user_bmi modify weight DOUBLE(10,2) COMMENT "user weight in KG on current date"');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
    }
}
