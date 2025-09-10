<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddLogDateColumnUserBmiTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('user_bmi', function (Blueprint $table) {
            $table->dateTime('log_date')->nullable()->after('bmi')->comment('store date at user bmi inserted.');
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
        if (Schema::hasTable('user_bmi')) {
            Schema::table('user_bmi', function (Blueprint $table) {
                if (Schema::hasColumn('user_bmi', 'log_date')) {
                    $table->dropColumn('log_date');
                }
            });
        }
        Schema::enableForeignKeyConstraints();
    }
}
