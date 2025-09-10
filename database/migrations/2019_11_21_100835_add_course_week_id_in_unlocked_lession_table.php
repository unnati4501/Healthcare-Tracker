<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddCourseWeekIdInUnlockedLessionTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('unlocked_user_course_lessons', function (Blueprint $table) {
            $table->unsignedBigInteger('course_week_id')->after('user_id')->nullable()->comment("refers to course_weeks table");
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
        if (Schema::hasTable('unlocked_user_course_lessons')) {
            Schema::table('unlocked_user_course_lessons', function (Blueprint $table) {
                if (Schema::hasColumn('unlocked_user_course_lessons', 'course_week_id')) {
                    $table->dropForeign('unlocked_user_course_lessons_course_week_id_foreign');
                    $table->dropColumn('course_week_id');
                }
            });
        }
        Schema::enableForeignKeyConstraints();
    }
}
