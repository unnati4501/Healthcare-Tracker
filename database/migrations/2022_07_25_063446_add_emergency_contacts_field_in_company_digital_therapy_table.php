<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddEmergencyContactsFieldInCompanyDigitalTherapyTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('company_digital_therapy', function (Blueprint $table) {
            $table->boolean('emergency_contacts')->default(0)->comment('1 if emergency contact is checked')->after('dt_max_sessions_company');
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
            $table->dropColumn('emergency_contacts');
        });
    }
}
