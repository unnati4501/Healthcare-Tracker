<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateCanceledAtFieldTypeInCronofyScheduleTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('cronofy_schedule', function (Blueprint $table) {
            $table->unsignedBigInteger('cancelled_by')->nullable()->comment("cancelled by if cancelled")->change();
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
        Schema::table('cronofy_schedule', function (Blueprint $table) {
            $table->string('cancelled_by', 255)->nullable()->comment('cancelled by if cancelled')->change();
        });
        Schema::enableForeignKeyConstraints();
    }
}
