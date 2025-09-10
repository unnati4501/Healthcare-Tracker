<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddedProfileIdFieldInCronofyCalendarTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('cronofy_calendar', function (Blueprint $table) {
            $table->text('profile_id')->comment("calendar profile id of cronofy")->after('calendar_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('cronofy_calendar', function (Blueprint $table) {
            $table->dropColumn('profile_id');
        });
    }
}
