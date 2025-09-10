<?php
namespace Database\Seeders;

use App\Models\MeditationTrack;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UpdateExistingMeditationTeam extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $meditationRecords = MeditationTrack::select('id')->get()->toArray();
        foreach ($meditationRecords as $value) {
            $checkRecords = DB::table('meditation_tracks_team')->where('meditation_track_id', $value['id'])->select('id')->count();

            if ($checkRecords <= 0) {
                $getCompanyRecords = DB::table('meditation_tracks_company')->join('team_location', 'team_location.company_id', '=', 'meditation_tracks_company.company_id')->where('meditation_tracks_company.meditation_track_id', $value['id'])->select('team_location.team_id')->get()->pluck('team_id')->toArray();

                $meditationTeam_input = [];

                foreach ($getCompanyRecords as $cValue) {
                    $meditationTeam_input[] = [
                        'meditation_track_id' => $value['id'],
                        'team_id'             => $cValue,
                    ];
                }
                DB::table('meditation_tracks_team')->insert($meditationTeam_input);
            }
        }
    }
}
