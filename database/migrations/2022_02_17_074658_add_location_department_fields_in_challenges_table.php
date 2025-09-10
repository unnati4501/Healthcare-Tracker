<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddLocationDepartmentFieldsInChallengesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('challenges', function (Blueprint $table) {
            $table->string('locations')->nullable()->default(null)->after('deep_link_uri')->comment('store multiple locations ids when open challenge');
            $table->string('departments')->nullable()->default(null)->after('locations')->comment('store multiple departments ids when open challenge');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('challenges', function (Blueprint $table) {
            $table->dropColumn('locations');
            $table->dropColumn('departments');
        });
    }
}
