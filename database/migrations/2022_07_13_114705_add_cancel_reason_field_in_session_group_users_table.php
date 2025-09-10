<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCancelReasonFieldInSessionGroupUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('session_group_users', function (Blueprint $table) {
            $table->dateTime('cancelled_at')->nullable()->comment('cancelled date if cancelled')->after('is_cancelled');
            $table->text('cancelled_reason')->nullable()->comment('cancelled reason if cancelled')->after('cancelled_at');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('session_group_users', function (Blueprint $table) {
            $table->dropColumn(['cancelled_at', 'cancelled_reason']);
        });
    }
}
