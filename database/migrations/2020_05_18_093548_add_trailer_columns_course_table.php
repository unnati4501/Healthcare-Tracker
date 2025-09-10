<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddTrailerColumnsCourseTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('courses', function (Blueprint $table) {
            $table->boolean('has_trailer')->default(0)->after('random_students')->comment('0 => no, 1 => yes');
            $table->tinyInteger('trailer_type')->default(0)->after('has_trailer')->comment('0 => Disabled, 1 => Audio, 2 => Video');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('courses', function (Blueprint $table) {
            $table->dropColumn('has_trailer');
            $table->dropColumn('trailer_type');
        });
    }
}
