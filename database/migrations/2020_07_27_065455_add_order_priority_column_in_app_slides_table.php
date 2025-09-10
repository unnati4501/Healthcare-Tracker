<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddOrderPriorityColumnInAppSlidesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('app_slides', function (Blueprint $table) {
            $table->integer('order_priority')->default(0)->after('content')->comment("default 0, flag for order priority");
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('app_slides', function (Blueprint $table) {
            $table->dropColumn('order_priority');
        });
    }
}
