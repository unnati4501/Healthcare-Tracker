<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateFieldInCompanyDigitalTherapyTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('company_digital_therapy', function (Blueprint $table) {
            $table->integer('set_hours_by')->default(1)->after('consent_url')->comment('1 => Company, 2 => Location');
            $table->integer('set_availability_by')->default(1)->after('set_hours_by')->comment('1 => General, 2 => Specific');
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
            $table->dropColumn(['set_hours_by', 'set_availability_by']);
        });
    }
}
