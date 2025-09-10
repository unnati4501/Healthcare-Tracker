<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class DropTagColumnMeditationTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('meditation_tracks', function (Blueprint $table) {
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
        Schema::table('meditation_tracks', function (Blueprint $table) {
            $table->string('tag', 255)->after('duration')->nullable()->comment('tag for record: move/nourish/inspire');
        });
    }
}
