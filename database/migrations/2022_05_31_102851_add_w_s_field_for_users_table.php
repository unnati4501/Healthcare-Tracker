<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddWSFieldForUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('is_authenticate')->default(false)->after('years_of_experience')->comment("this is fields is use for WS have set calendar or not");
            $table->boolean('is_availability')->default(false)->after('years_of_experience')->comment("this is fields is use for WS have set availability or not");
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('is_authenticate');
            $table->dropColumn('is_availability');
        });
    }
}
