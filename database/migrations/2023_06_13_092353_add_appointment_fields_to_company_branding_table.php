<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddAppointmentFieldsToCompanyBrandingTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('company_branding', function (Blueprint $table) {
            $table->string('appointment_title', 100)->default('Appointments')->after('dt_description')->comment('title of appointment tab for portal');
            $table->text('appointment_description')->nullable()->after('appointment_title')->comment('appointment tab description for portal');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('company_branding', function (Blueprint $table) {
            $table->dropColumn('appointment_title');
            $table->dropColumn('appointment_description');
        });
    }
}
