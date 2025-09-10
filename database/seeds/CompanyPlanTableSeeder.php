<?php
namespace Database\Seeders;

use App\Http\Traits\DisableForeignKeys;
use App\Http\Traits\TruncateTable;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\CpFeatures;

class CompanyPlanTableSeeder extends Seeder
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

        // truncate table
        $this->truncateMultiple(['cp_features']);

        $cpPlanList = \json_decode(
            \file_get_contents(
                __DIR__ . '/data/cp_plan.json'
            ),
            true
        );

        $cpPlan = [];

        $sort = 1;
        $now  = Carbon::now();
        foreach ($cpPlanList as $value) {
            $cpPlan[] = [
                'parent_id'  => $value['parent_id'],
                'name'       => $value['name'],
                'slug'       => str_slug($value['name']),
                'manage'     => $value['manage'],
                'group'      => (!empty($value['group']) ? 2 : 1),
                'status'     => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ];
            $sort++;
        }

        try {
            DB::beginTransaction();

            DB::statement('SET FOREIGN_KEY_CHECKS=0;');
            DB::table('cp_features')->truncate();
            DB::statement('SET FOREIGN_KEY_CHECKS=1;');

            foreach ($cpPlan as $plan) {
                CpFeatures::create($plan);
            }
            
            DB::commit();
        } catch (\Exception $exception) {
            DB::rollBack();
            echo $exception->getMessage();
        }

        // Enable foreign key checks!
        $this->enableForeignKeys();
    }
}
