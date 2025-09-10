<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddTypeColumnToAppSlidesTable extends Migration
{
    /**
     * Custructor
     *
     * @return Null
     */
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
        Schema::table('app_slides', function (Blueprint $table) {
            $table->enum('type', array('app', 'portal'))->default('app')->comment('Data differentise between app and portal')->after('order_priority');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (Schema::hasTable('app_slides')) {
            Schema::table('app_slides', function (Blueprint $table) {
                if (Schema::hasColumn('app_slides', 'type')) {
                    $table->dropColumn('type');
                }
            });
        }
    }
}
