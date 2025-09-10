<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddLocationDepartmentsFieldsInEapListTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('eap_list', function (Blueprint $table) {
            $table->string('locations')->nullable()->default(null)->after('deep_link_uri')->comment('store multiple locations ids for ZCA and RCA company');
            $table->string('departments')->nullable()->default(null)->after('locations')->comment('store multiple departments ids for ZCA and RCA company');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('eap_list', function (Blueprint $table) {
            $table->dropColumn('locations');
            $table->dropColumn('departments');
        });
    }
}
