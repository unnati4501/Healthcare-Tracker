<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddPortalBannerFiledsToCompanyBrandingTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('company_branding', function (Blueprint $table) {
            $table->longText('portal_sub_description')->nullable()->default(null)->after('portal_description')->comment('Sub description of portal');
            $table->longText('portal_footer_header_text')->nullable()->default(null)->after('portal_footer_text')->comment('header(top) text of the footer');
            $table->boolean('exclude_gender_and_dob')->default(false)->after('portal_footer_header_text')->comment('true, hide dob and gender from the portal.');
            $table->string('dt_title')->nullable()->default(null)->after('exclude_gender_and_dob')->comment('Dt title to display on portal');
            $table->longText('dt_description')->nullable()->default(null)->after('dt_title')->comment('Dt description of portal');
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
            $table->dropColumn('portal_sub_description');
            $table->dropColumn('portal_footer_header_text');
            $table->dropColumn('exclude_gender_and_dob');
            $table->dropColumn('dt_title');
            $table->dropColumn('dt_description');
        });
    }
}
