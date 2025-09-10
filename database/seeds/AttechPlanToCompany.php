<?php
namespace Database\Seeders;

use App\Http\Traits\DisableForeignKeys;
use App\Http\Traits\TruncateTable;
use App\Models\Company;
use Illuminate\Database\Seeder;
use App\Models\CpPlan;

/**
 * Class AttechPlanToCompany.
 */
class AttechPlanToCompany extends Seeder
{
    use DisableForeignKeys, TruncateTable;
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Disable foreign key checks!
        $this->disableForeignKeys();

        DB::table('cp_company_plans')->truncate();

        $companyList = Company::whereNull('parent_id')
            ->where('is_reseller', false)
            ->pluck('id')
            ->toArray();

        CpPlan::where('slug', 'challenge')->first()->plancompany()->sync($companyList);

        // Enable foreign key checks!
        $this->enableForeignKeys();
    }
}
