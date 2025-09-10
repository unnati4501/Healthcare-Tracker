<?php
namespace Database\Seeders;

use App\Models\Company;
use App\Models\MeditationTrack;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class UpdateCompanyVisibilityMeditationTracksSeeder extends Seeder
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
            $meditationRecords = MeditationTrack::select('id')->get();
            $company           = Company::select('id')->where('subscription_start_date', '<=', Carbon::now())->pluck('id')->toArray();

            if ($meditationRecords) {
                foreach ($meditationRecords as $value) {
                    foreach ($company as $companyId) {
                        DB::insert('insert into meditation_tracks_company (meditation_track_id, company_id, created_at, updated_at) values(?, ?, ?, ?)', [$value->id, $companyId, now(), now()]);
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
