<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;

class UpdatePlanForResellerCompanies extends Seeder
{
    /**
     * This will convert all existing slots timezone to hc's timezone from UTC
     *
     * @return void
     */
    public function run()
    {
        try {
            $data       = DB::table('companies')->select('companies.id')->where('companies.is_reseller', 1)->whereNull('companies.parent_id')
                        ->whereNotIn('companies.id', function ($query) {
                            $query->select(DB::raw('DISTINCT(company_id)'))->from('cp_company_plans');
                        })->get()->toArray();
            $portalPlan = DB::table('cp_plan')->select('id')->where('slug', 'portal-standard')->where('group', 2)->get()->first();
           
            foreach ($data as $value) {
                \DB::table('cp_company_plans')->insert([
                    'plan_id'       => $portalPlan->id,
                    'company_id'    => $value->id,
                ]);
            }
            
        } catch (\Illuminate\Database\QueryException $e) {
            $this->command->error("SQL Error: " . $e->getMessage() . "\n");
        }
    }
}
