<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class DropTagColumnFeedsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('feeds', function (Blueprint $table) {
            $table->dropColumn('tag');
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
            $table->string('tag', 255)->after('description')->nullable()->comment('tag for record: move/nourish/inspire');
        });
    }
}
