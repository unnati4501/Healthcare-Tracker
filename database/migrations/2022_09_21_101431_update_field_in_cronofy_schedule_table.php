<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateFieldInCronofyScheduleTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('cronofy_schedule', function (Blueprint $table) {
            $table->string('event_identifier')->nullable()->change();
            $table->string('token')->nullable()->comment('Store token for UI Element of cronofy')->after('event_identifier');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('cronofy_schedule', function (Blueprint $table) {
            $table->string('event_identifier')->change();
            $table->dropColumn('token');
        });
    }
}
