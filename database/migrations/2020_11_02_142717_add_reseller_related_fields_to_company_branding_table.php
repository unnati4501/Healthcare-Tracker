<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddResellerRelatedFieldsToCompanyBrandingTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('company_branding', function (Blueprint $table) {
            $table->string('portal_domain', 255)->nullable()->default(null)->after('sub_domain')->comment("size of the company");
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (Schema::hasTable('company_branding')) {
            Schema::table('company_branding', function (Blueprint $table) {
                if (Schema::hasColumn('company_branding', 'portal_domain')) {
                    $table->dropColumn('portal_domain');
                }
            });
        }
    }
}
