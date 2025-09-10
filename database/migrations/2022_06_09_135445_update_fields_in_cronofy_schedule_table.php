<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateFieldsInCronofyScheduleTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('cronofy_schedule', function (Blueprint $table) {
            $table->text('event_id')->comment('Event Id created event in cronofy and based on that schedule event.')->after('id');
            $table->text('scheduling_id')->comment('Sheduling id is get from cronofy API when event is schedule.')->after('event_id');
            $table->dropColumn('cancel_url');
            $table->dropColumn('reschedule_url');
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
            $table->dropColumn('event_id');
            $table->dropColumn('scheduling_id');
            $table->string('cancel_url', 255)->comment('cancel url of the event');
            $table->string('reschedule_url', 255)->comment('reschedule url of the event');
        });
    }
}
