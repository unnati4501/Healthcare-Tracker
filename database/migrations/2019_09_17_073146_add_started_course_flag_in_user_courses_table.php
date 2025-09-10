<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddStartedCourseFlagInUserCoursesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('user_course', function (Blueprint $table) {
            $table->boolean('started_course', false)->comment("flag to check user started course or not")->after('joined_on');
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
        if (Schema::hasTable('user_course')) {
            Schema::table('user_course', function (Blueprint $table) {
                if (Schema::hasColumn('user_course', 'started_course')) {
                    $table->dropColumn('started_course');
                }
            });
        }
        Schema::enableForeignKeyConstraints();
    }
}
