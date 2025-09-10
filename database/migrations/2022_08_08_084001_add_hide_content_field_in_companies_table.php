<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddHideContentFieldInCompaniesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->boolean('hide_content')->default(false)->after('eap_tab')->comment('true, hide content from the portal.');
        });
        Schema::table('company_digital_therapy', function (Blueprint $table) {
            $table->boolean('consent')->default(false)->after('emergency_contacts')->comment('true, send email to user for fill the content form.');
            $table->longText('consent_url')->nullable()->after('consent')->comment("content url to send email to user with email content.");
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
            $table->dropColumn('hide_content');
        });
        Schema::table('company_digital_therapy', function (Blueprint $table) {
            $table->dropColumn(['consent', 'consent_url']);
        });
    }
}
