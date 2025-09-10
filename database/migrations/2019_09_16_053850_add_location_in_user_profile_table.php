<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddLocationInUserProfileTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('user_profile', function (Blueprint $table) {
            $table->string('location', 255)->nullable()->comment("location of user");
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
        if (Schema::hasTable('user_profile')) {
            Schema::table('user_profile', function (Blueprint $table) {
                if (Schema::hasColumn('user_profile', 'location')) {
                    $table->dropColumn('location');
                }
            });
        }
        Schema::enableForeignKeyConstraints();
    }
}
