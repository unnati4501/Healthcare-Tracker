<?php
namespace Database\Seeders;

use App\Models\Company;
use App\Models\Webinar;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class UpdateCompanyVisibilityWebinarRecords extends Seeder
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
            $webinarRecords = Webinar::select('id')->get();
            $company        = Company::select('id')->where('subscription_start_date', '<=', Carbon::now())->pluck('id')->toArray();

            if ($webinarRecords) {
                foreach ($webinarRecords as $value) {
                    foreach ($company as $companyId) {
                        DB::insert('insert into webinar_company (webinar_id, company_id, created_at, updated_at) values(?, ?, ?, ?)', [$value->id, $companyId, now(), now()]);
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
