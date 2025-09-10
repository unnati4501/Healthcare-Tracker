<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddJoinedFlagInUserCoursesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('user_course', function (Blueprint $table) {
            $table->boolean('joined', false)->comment("flag to check user joined course or not")->after('user_id');
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
                if (Schema::hasColumn('user_course', 'joined')) {
                    $table->dropColumn('joined');
                }
            });
        }
        Schema::enableForeignKeyConstraints();
    }
}
