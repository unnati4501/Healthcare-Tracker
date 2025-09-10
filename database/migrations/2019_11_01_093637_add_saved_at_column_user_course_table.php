<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddSavedAtColumnUserCourseTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('user_course', function (Blueprint $table) {
            $table->timestamp('saved_at')->nullable()->after('saved')->comment('store date at which user saved course.');
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
                if (Schema::hasColumn('user_course', 'saved_at')) {
                    $table->dropColumn('saved_at');
                }
            });
        }
        Schema::enableForeignKeyConstraints();
    }
}
