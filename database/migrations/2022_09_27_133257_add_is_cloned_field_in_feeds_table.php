<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddIsClonedFieldInFeedsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('feeds', function (Blueprint $table) {
            $table->boolean('is_cloned')->default(false)->after('is_stick')->comment('true, that means this story is cloned from zca');
            $table->integer('cloned_from')->default(0)->after('is_cloned')->comment('From which story this is cloned');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('feeds', function (Blueprint $table) {
            $table->dropColumn('is_cloned');
            $table->dropColumn('cloned_from');
        });
    }
}
