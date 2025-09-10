<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddReminderAtToEapCalendly extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('eap_calendly', function (Blueprint $table) {
            $table->dateTime('reminder_at')->nullable()->default(null)->comment('Date and time when 15 mins reminder sent before start time of session')->after('status');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('eap_calendly', function (Blueprint $table) {
            $table->dropColumn('reminder_at');
        });
    }
}
