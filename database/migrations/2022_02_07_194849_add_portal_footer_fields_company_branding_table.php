<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddPortalFooterFieldsCompanyBrandingTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('company_branding', function (Blueprint $table) {
            $table->json('portal_footer_json')->nullable()->default(null)->after('portal_description')->comment('json for footer columns');
            $table->string('portal_footer_text')->nullable()->default(null)->after('portal_footer_json')->comment('bottom text of the footer');
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
            $table->dropColumn('portal_footer_json');
            $table->dropColumn('portal_footer_text');
        });
    }
}
