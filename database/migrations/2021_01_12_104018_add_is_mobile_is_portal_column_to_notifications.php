<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddIsMobileIsPortalColumnToNotifications extends Migration
{
    public function __construct()
    {
        DB::getDoctrineSchemaManager()->getDatabasePlatform()->registerDoctrineTypeMapping('enum', 'string');
    }
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('notifications', function (Blueprint $table) {
            $table->tinyInteger('is_mobile')->default(1)->after('push')->comment('it\'s will be decided for mobile notifications. 0 => disable, 1 => enable');
            $table->tinyInteger('is_portal')->default(1)->after('is_mobile')->comment('it\'s will be decided for portal notifications. 0 => disable, 1 => enable');
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
            $table->dropColumn('is_mobile');
            $table->dropColumn('is_portal');
        });
    }
}
