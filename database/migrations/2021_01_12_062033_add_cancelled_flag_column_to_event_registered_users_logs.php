<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCancelledFlagColumnToEventRegisteredUsersLogs extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('event_registered_users_logs', function (Blueprint $table) {
            $table->boolean('is_cancelled')->default(false)->after('user_id')->comment('this flag will be true then event don\'t show in user list');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('event_registered_users_logs', function (Blueprint $table) {
            $table->dropColumn('is_cancelled');
        });
    }
}
