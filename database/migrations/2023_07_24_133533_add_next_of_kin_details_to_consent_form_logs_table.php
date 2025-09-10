<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddNextOfKinDetailsToConsentFormLogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('consent_form_logs', function (Blueprint $table) {
            $table->text('fullname')->nullable()->comment("Name and Surname (Next of kin details)")->after('email');
            $table->text('address')->nullable()->comment("Address (Next of kin details)")->after('fullname');
            $table->text('relation')->nullable()->comment("Relationship (Next of kin details)")->after('address');
            $table->text('contact_no')->nullable()->comment("Contact No (Next of kin details)")->after('relation');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('consent_form_logs', function (Blueprint $table) {
            $table->dropColumn('fullname', 'address', 'relation', 'contact_no');
        });
    }
}
