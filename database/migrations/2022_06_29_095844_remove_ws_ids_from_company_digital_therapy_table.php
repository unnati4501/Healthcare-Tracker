<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RemoveWsIdsFromCompanyDigitalTherapyTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('company_digital_therapy', function (Blueprint $table) {
            $table->dropColumn('dt_wellbeing_sp_ids');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('company_digital_therapy', function (Blueprint $table) {
            $table->string('dt_wellbeing_sp_ids')->nullable()->comment('get ids of wellbeing specialist')->after('dt_is_onsite');
        });
    }
}
