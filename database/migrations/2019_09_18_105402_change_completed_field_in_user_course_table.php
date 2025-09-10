<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ChangeCompletedFieldInUserCourseTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('user_course', function (Blueprint $table) {
            $table->dropColumn('completed_course');

            $table->boolean('completed', false)->comment("flag to check user completed course or not")->after('user_id');
            $table->timestamp('completed_on')->nullable()->after('completed')->comment("datetime when user completed the course");
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
                if (Schema::hasColumn('user_course', 'completed')) {
                    $table->dropColumn('completed');
                }
                if (Schema::hasColumn('user_course', 'completed_on')) {
                    $table->dropColumn('completed_on');
                }
            });
        }
        Schema::enableForeignKeyConstraints();
    }
}
