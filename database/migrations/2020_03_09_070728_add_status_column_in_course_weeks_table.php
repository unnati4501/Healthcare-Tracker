<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddStatusColumnInCourseWeeksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('course_weeks', function (Blueprint $table) {
            $table->boolean('status')->default(1)->comment('1 => publish, 0 => unpublish')->after('is_default');
        });

        DB::statement("ALTER TABLE course_weeks ALTER status SET DEFAULT 0");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('course_weeks', function (Blueprint $table) {
            $table->dropColumn('status');
        });
    }
}
