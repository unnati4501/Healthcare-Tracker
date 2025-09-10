<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateRsaRcaRolesName extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('roles')) {
            Schema::table('roles', function (Blueprint $table) {
                DB::statement("UPDATE `roles` SET `name` = 'Reseller Super Admin' WHERE `slug` = 'reseller_super_admin'");
                DB::statement("UPDATE `roles` SET `name` = 'Reseller Company Admin' WHERE `slug` = 'reseller_company_admin'");
            });
        }
    }
}
