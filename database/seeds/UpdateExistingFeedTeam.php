<?php
namespace Database\Seeders;

use App\Models\Feed;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UpdateExistingFeedTeam extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $feedRecords = Feed::select('id')->get()->toArray();
        foreach ($feedRecords as $value) {
            $checkRecords = DB::table('feed_team')->where('feed_id', $value['id'])->select('id')->count();

            if ($checkRecords <= 0) {
                $getCompanyRecords = DB::table('feed_company')->join('team_location', 'team_location.company_id', '=', 'feed_company.company_id')->where('feed_company.feed_id', $value['id'])->select('team_location.team_id')->get()->pluck('team_id')->toArray();

                $feedTeam_input = [];

                foreach ($getCompanyRecords as $cValue) {
                    $feedTeam_input[] = [
                        'feed_id' => $value['id'],
                        'team_id' => $cValue,
                    ];
                }
                DB::table('feed_team')->insert($feedTeam_input);
            }
        }
    }
}
