<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddNewFiledGroupForCpFeaturesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('cp_features', function (Blueprint $table) {
            $table->tinyInteger('group')->default(1)->after('manage')->comment('1 => Company, 2 => Reseller');
            $table->dropUnique(['name']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('cp_features', function (Blueprint $table) {
            $table->string('name', 191)->unique()->change();
            $table->dropColumn('group');
        });
    }
}
