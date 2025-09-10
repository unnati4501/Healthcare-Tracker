<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateEventBookingLogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::disableForeignKeyConstraints();
        Schema::table('event_booking_logs', function (Blueprint $table) {
            $table->dropForeign('event_booking_logs_presenter_user_id_foreign');
            $table->dropForeign('event_booking_logs_user_id_foreign');
            $table->dropColumn('user_id');
            $table->date('booking_date')->default(null)->nullable()->comment("date of event is booked")->change();
            $table->index(['booking_date', 'start_time', 'end_time']);
            $table->text('notes')->nullable()->comment('Additional notes added by admin while booking an event')->after('end_time');
            $table->json('meta')->nullable()->comment('To store meta data as JSON like presenter name and other details')->after('notes');
        });
        Schema::enableForeignKeyConstraints();
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::disableForeignKeyConstraints();
        Schema::table('event_booking_logs', function (Blueprint $table) {
            $table->dropColumn('notes');
            $table->dropColumn('meta');
            $table->dropIndex('event_booking_logs_booking_date_start_time_end_time_index');
            $table->unsignedBigInteger('user_id')->nullable()->after('presenter_user_id')->comment("refers to users table and event is booked for this user, here if user id is null means event is booked for all the users of company");
            $table->foreign('presenter_user_id')->references('id')->on('users');
            $table->foreign('user_id')->references('id')->on('users');
        });
        Schema::enableForeignKeyConstraints();
    }
}
