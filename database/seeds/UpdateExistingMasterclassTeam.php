<?php
namespace Database\Seeders;

use App\Models\Course;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UpdateExistingMasterclassTeam extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $courseRecords = Course::select('id')->get()->toArray();
        foreach ($courseRecords as $value) {
            $checkRecords = DB::table('masterclass_team')->where('masterclass_id', $value['id'])->select('id')->count();

            if ($checkRecords <= 0) {
                $getCompanyRecords = DB::table('masterclass_company')->join('team_location', 'team_location.company_id', '=', 'masterclass_company.company_id')->where('masterclass_company.masterclass_id', $value['id'])->select('team_location.team_id')->get()->pluck('team_id')->toArray();

                $courseTeam_input = [];

                foreach ($getCompanyRecords as $cValue) {
                    $courseTeam_input[] = [
                        'masterclass_id' => $value['id'],
                        'team_id'        => $cValue,
                    ];
                }
                DB::table('masterclass_team')->insert($courseTeam_input);
            }
        }
    }
}
