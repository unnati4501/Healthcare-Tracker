<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateDatatypeForCompanyBrandingTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('company_branding', function (Blueprint $table) {
            $table->longText('portal_footer_json')->nullable()->change();
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
            $table->json('portal_footer_json')->nullable()->after('portal_description')->change();
        });
    }
}
