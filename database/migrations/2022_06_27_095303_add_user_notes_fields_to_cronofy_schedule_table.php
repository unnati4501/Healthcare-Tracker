<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddUserNotesFieldsToCronofyScheduleTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('cronofy_schedule', function (Blueprint $table) {
            $table->longText('user_notes')->nullable()->after('notes')->comment("Notes added by user when booing session from mobile app");
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
            $table->dropColumn('user_notes');
        });
    }
}
