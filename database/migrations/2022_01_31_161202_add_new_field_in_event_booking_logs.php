<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddNewFieldInEventBookingLogs extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('event_booking_logs', function (Blueprint $table) {
            $table->enum('company_type', ['Zevo', 'Company'])->default('Zevo')->comment('Check company type')->after('csat_at');
            $table->string('video_link', 255)->nullable()->comment('Video Link')->after('company_type');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('event_booking_logs', function (Blueprint $table) {
            $table->dropColumn('company_type');
            $table->dropColumn('video_link');
        });
    }
}
