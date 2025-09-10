<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddPortalDomainFieldsCompanyBrandingTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('company_branding', function (Blueprint $table) {
            $table->string('portal_theme', 255)->nullable()->default(null)->after('portal_domain')->comment("Theme of portal");
            $table->string('portal_title', 255)->nullable()->default(null)->after('portal_theme')->comment("Title in portal onboarding page");
            $table->longText('portal_description')->nullable()->default(null)->after('portal_title')->comment("Description in portal onboarding page");
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
            $table->dropColumn('portal_theme');
            $table->dropColumn('portal_title');
            $table->dropColumn('portal_description');
        });
    }
}
