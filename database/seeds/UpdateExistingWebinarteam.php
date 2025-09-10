<?php
namespace Database\Seeders;

use App\Models\Webinar;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UpdateExistingWebinarteam extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $webinarRecords = Webinar::select('id')->get()->toArray();
        foreach ($webinarRecords as $value) {
            $checkRecords = DB::table('webinar_team')->where('webinar_id', $value['id'])->select('id')->count();

            if ($checkRecords <= 0) {
                $getCompanyRecords = DB::table('webinar_company')->join('team_location', 'team_location.company_id', '=', 'webinar_company.company_id')->where('webinar_company.webinar_id', $value['id'])->select('team_location.team_id')->get()->pluck('team_id')->toArray();

                $webinarTeam_input = [];

                foreach ($getCompanyRecords as $cValue) {
                    $webinarTeam_input[] = [
                        'webinar_id' => $value['id'],
                        'team_id'    => $cValue,
                    ];
                }
                DB::table('webinar_team')->insert($webinarTeam_input);
            }
        }
    }
}
