<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddSentOnToNotificationUserTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('notification_user', function (Blueprint $table) {
            $table->timestamp('sent_on')->nullable()->after('sent')->comment("datetime when notification sent to user");
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::disableForeignKeyConstraints();
        if (Schema::hasTable('notification_user')) {
            Schema::table('notification_user', function (Blueprint $table) {
                if (Schema::hasColumn('notification_user', 'sent_on')) {
                    $table->dropColumn('sent_on');
                }
            });
        }
        Schema::enableForeignKeyConstraints();
    }
}
