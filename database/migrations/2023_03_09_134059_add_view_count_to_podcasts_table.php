<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddViewCountToPodcastsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('podcasts', function (Blueprint $table) {
            $table->Integer('view_count')->default(0)->after('tag')->comment('Audio count when user tep on that.');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('podcasts', function (Blueprint $table) {
            $table->dropColumn('view_count');
        });
    }
}
