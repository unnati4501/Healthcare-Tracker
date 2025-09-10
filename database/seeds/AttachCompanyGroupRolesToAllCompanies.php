<?php
namespace Database\Seeders;

use App\Models\Company;
use App\Models\Role;
use Illuminate\Database\Seeder;

class AttachCompanyGroupRolesToAllCompanies extends Seeder
{
    /**
     * Run the database seeds to attach company group roles to all comapnies.
     *
     * @return void
     */
    public function run()
    {
        try {
            $companies         = Company::where('is_reseller', 0)->whereNull('parent_id')->select('id')->get();
            $companyGroupRoles = Role::where(['group' => 'company', 'default' => 0])->get()->pluck('id')->toArray();
            if (sizeof($companyGroupRoles) > 0) {
                foreach ($companies as $company) {
                    $company->resellerRoles()->sync($companyGroupRoles);
                }
            }
        } catch (\Illuminate\Database\QueryException $e) {
            $this->command->error("SQL Error: " . $e->getMessage() . "\n");
        } catch (\Exception $exception) {
            report($exception);
            $this->command->error($exception->getMessage());
        }
    }
}
