<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateDescriptionFieldInCpPlanTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('cp_plan', function (Blueprint $table) {
            $table->string('description', 255)->nullable()->comment("description for the plan")->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('cp_plan', function (Blueprint $table) {
            $table->string('description', 255)->comment("description for the plan")->change();
        });
    }
}
