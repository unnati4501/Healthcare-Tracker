<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddAutoTeamCreatilsDetailsColumnToDepartmentLocationTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('department_location', function (Blueprint $table) {
            if (Schema::hasColumn('department_location', 'team_naming_convention')) {
                $table->dropColumn('team_naming_convention');
            }
            $table->json('auto_team_creation_meta')->nullable()->default(null)->comment('to store auto team cration details like number of employee, possible teams, and naming_convention')->after('department_id');
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
            $table->dropColumn('auto_team_creation_meta');
            if (!Schema::hasColumn('department_location', 'team_naming_convention')) {
                $table->string('team_naming_convention', 255)->nullable()->comment('convention will be used while auto team creation')->after('department_id');
            }
        });
    }
}
