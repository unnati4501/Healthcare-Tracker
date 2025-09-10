<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddLogDateColumnUserWeightTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('user_weight', function (Blueprint $table) {
            $table->dateTime('log_date')->nullable()->after('weight')->comment('store date at user weight inserted.');
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
        if (Schema::hasTable('user_weight')) {
            Schema::table('user_weight', function (Blueprint $table) {
                if (Schema::hasColumn('user_weight', 'log_date')) {
                    $table->dropColumn('log_date');
                }
            });
        }
        Schema::enableForeignKeyConstraints();
    }
}
