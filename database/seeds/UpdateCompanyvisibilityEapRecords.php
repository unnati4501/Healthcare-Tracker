<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Company;
use Carbon\Carbon;

class UpdateCompanyvisibilityEapRecords extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $eapRecords = DB::select('SELECT id, company_id FROM `eap_list` WHERE id NOT IN ( SELECT eap_id FROM `eap_company` group by eap_id )');

        if ($eapRecords) {
            foreach ($eapRecords as $value) {
                if ($value->company_id == null) {
                    $companyRecords = Company::where('subscription_start_date', '<=', Carbon::now())->pluck('name', 'id')->toArray();
                } else {
                    $companyRecords = Company::where('id', $value->company_id)->orwhere('parent_id', $value->company_id)->where('subscription_start_date', '<=', Carbon::now())->pluck('name', 'id')->toArray();
                }

                foreach ($companyRecords as $companykey => $companyvalue) {
                    DB::insert('insert into eap_company (eap_id, company_id, created_at, updated_at) values(?, ?, ?, ?)', [$value->id, $companykey, now(), now()]);
                }
            }
        }
    }
}
