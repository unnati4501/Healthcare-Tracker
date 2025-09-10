<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateSubdomainKeyForCompanyBrandingTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('company_branding', function (Blueprint $table) {
            $table->string('sub_domain', 80)->nullable()->comment('domain name of company branding')->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
        Schema::table('company_branding', function (Blueprint $table) {
            $table->string('sub_domain', 80)->unique()->comment('domain name of company branding')->change();
        });
    }
}
