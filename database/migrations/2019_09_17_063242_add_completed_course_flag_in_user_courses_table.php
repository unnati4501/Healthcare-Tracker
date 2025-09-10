<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCompletedCourseFlagInUserCoursesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('user_course', function (Blueprint $table) {
            $table->timestamp('joined_on')->nullable()->after('joined')->comment("datetime when user joined the course");
            $table->boolean('completed_course', false)->comment("flag to check user completed course or not")->after('user_id');
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
                if (Schema::hasColumn('user_course', 'joined_on')) {
                    $table->dropColumn('joined_on');
                }
                if (Schema::hasColumn('user_course', 'completed_course')) {
                    $table->dropColumn('completed_course');
                }
            });
        }
        Schema::enableForeignKeyConstraints();
    }
}
