<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddTimezoneColumnFeedTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('feeds', function (Blueprint $table) {
            $table->string('timezone', 255)->default('UTC')->after('title')->comment('timezone in which feed is published');
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
        if (Schema::hasTable('feeds')) {
            Schema::table('feeds', function (Blueprint $table) {
                if (Schema::hasColumn('feeds', 'timezone')) {
                    $table->dropColumn('timezone');
                }
            });
        }
        Schema::enableForeignKeyConstraints();
    }
}
