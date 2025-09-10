<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddMemberAcceptDeclineStatusToGroupMembersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('group_members', function (Blueprint $table) {
            $table->boolean('accept_decline_status')->default(0)->comment('Default 0 => Send invitation, 1 => Accepted invitation, 2 => Declined invitation ')->after('status');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('group_members', function (Blueprint $table) {
            $table->dropColumn('accept_decline_status');
        });
    }
}
