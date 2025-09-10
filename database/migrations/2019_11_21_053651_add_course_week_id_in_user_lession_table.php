<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddCourseWeekIdInUserLessionTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('user_lession', function (Blueprint $table) {
            $table->unsignedBigInteger('course_week_id')->after('course_id')->nullable()->comment("refers to course_weeks table");
            $table->foreign('course_week_id')
                ->references('id')->on('course_weeks')
                ->onDelete('cascade');
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
        if (Schema::hasTable('user_lession')) {
            Schema::table('user_lession', function (Blueprint $table) {
                if (Schema::hasColumn('user_lession', 'course_week_id')) {
                    $table->dropForeign('user_lession_course_week_id_foreign');
                    $table->dropColumn('course_week_id');
                }
            });
        }
        Schema::enableForeignKeyConstraints();
    }
}
