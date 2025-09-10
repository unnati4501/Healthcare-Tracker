<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddAutoTeamCreationColumnsToCompaniesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->boolean('auto_team_creation')->default(false)->comment('0 => Disabled, 1 => Enabled')->after('group_restriction');
            $table->integer('team_limit')->default(0)->comment('Number of members in a team this will be used when auto team creation is enable')->after('auto_team_creation');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->dropColumn('auto_team_creation');
            $table->dropColumn('team_limit');
        });
    }
}
