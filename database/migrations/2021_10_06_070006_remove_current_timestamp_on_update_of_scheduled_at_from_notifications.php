<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RemoveCurrentTimestampOnUpdateOfScheduledAtFromNotifications extends Migration
{
    public function __construct()
    {
        \DB::getDoctrineSchemaManager()->getDatabasePlatform()->registerDoctrineTypeMapping('enum', 'string');
    }

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('notifications', function (Blueprint $table) {
            \DB::statement("ALTER TABLE `notifications` CHANGE `scheduled_at` `scheduled_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP;");
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('notifications', function (Blueprint $table) {
        });
    }
}
