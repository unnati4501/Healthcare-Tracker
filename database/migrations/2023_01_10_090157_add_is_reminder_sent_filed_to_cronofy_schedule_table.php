<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddIsReminderSentFiledToCronofyScheduleTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('cronofy_schedule', function (Blueprint $table) {
            $table->boolean('is_reminder_sent')->default(false)->comment("set flag for is session notes reminder email")->after('no_show');
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
            $table->dropColumn('is_reminder_sent');
        });
    }
}
