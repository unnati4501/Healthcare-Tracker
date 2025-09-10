<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateCourseLessonTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('course_lessions', function (Blueprint $table) {
            $table->dropColumn('type');
        });

        Schema::table('course_lessions', function (Blueprint $table) {
            $table->longText('description')->comment('lession description')->nullable()->change();
            $table->enum('type', ['1', '2', '3', '4'])->comment('1 => Audio, 2 => Video, 3 => Youtube Link, 4 => Content')->nullable()->after('order_priority');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
