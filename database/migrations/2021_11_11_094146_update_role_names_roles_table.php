<?php

use App\Models\Role;
use Illuminate\Database\Migrations\Migration;

class UpdateRoleNamesRolesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Role::where('slug', 'super_admin')
            ->update(['name' => 'Zevo Super Admin']);

        Role::where('slug', 'company_admin')
            ->update(['name' => 'Zevo Company Admin']);

        Role::where('slug', 'reseller_super_admin')
            ->update(['name' => 'Super Admin']);

        Role::where('slug', 'reseller_company_admin')
            ->update(['name' => 'Company Admin']);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Role::where('slug', 'super_admin')
            ->update(['name' => 'Super Admin']);

        Role::where('slug', 'company_admin')
            ->update(['name' => 'Company Admin']);

        Role::where('slug', 'reseller_super_admin')
            ->update(['name' => 'Reseller Super Admin']);

        Role::where('slug', 'reseller_company_admin')
            ->update(['name' => 'Reseller Company Admin']);
    }
}
