<?php
namespace Database\Seeders;

use App\Models\Company;
use App\Models\Recipe;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class UpdateCompanyVisibilityRecipeRecords extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        try {
            \DB::beginTransaction();
            $recipeRecords = Recipe::select('id', 'company_id')->get();
            $company       = Company::select('id')->where('subscription_start_date', '<=', Carbon::now())->pluck('id')->toArray();
            if ($recipeRecords) {
                foreach ($recipeRecords as $value) {
                    $companysId = $value->company_id;
                    if ($companysId != null) {
                        $company = Company::select('id')->where('id', $companysId)->orwhere('parent_id', $companysId)->where('subscription_start_date', '<=', Carbon::now())->pluck('id')->toArray();
                    }
                    foreach ($company as $companyId) {
                        DB::insert('insert into recipe_company (recipe_id, company_id, created_at, updated_at) values(?, ?, ?, ?)', [$value->id, $companyId, now(), now()]);
                    }
                }
            }
            \DB::commit();
        } catch (\Exception $exception) {
            \DB::rollBack();
            \Log::critical(__CLASS__, [__FILE__, __LINE__, $exception->getMessage()]);
        }
    }
}
