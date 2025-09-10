<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddFavouriteFieldsToUserCourse extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('user_course', function (Blueprint $table) {
            $table->boolean('favourited')->default(false)->comment('true, if course is favourited by user')->after('liked');
            $table->timestamp('favourited_at')->nullable()->comment('store date at which user favourited course.')->after('favourited');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('user_course', function (Blueprint $table) {
            $table->dropColumn('favourited');
            $table->dropColumn('favourited_at');
        });
    }
}
