<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddTeamNamingConventionColumnToDepartmentLocationTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('department_location', function (Blueprint $table) {
            $table->string('team_naming_convention', 255)->nullable()->comment('convention will be used while auto team creation')->after('department_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('department_location', function (Blueprint $table) {
            $table->dropColumn('team_naming_convention');
        });
    }
}
