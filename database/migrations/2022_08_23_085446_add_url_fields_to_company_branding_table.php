<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddUrlFieldsToCompanyBrandingTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('company_branding', function (Blueprint $table) {
            $table->string('terms_url')->nullable()->comment('Terms of use url will display on portal register')->after('portal_description');
            $table->string('privacy_policy_url')->nullable()->comment('Privacy and policy url will display on portal register')->after('terms_url');
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
            $table->dropColumn('terms_url');
            $table->dropColumn('privacy_policy_url');
        });
    }
}
