<?php
namespace Database\Seeders;

use App\Models\Company;
use App\Models\UserStep;
use Illuminate\Database\Seeder;

class GenerateDummyStepsDataForICReportExport extends Seeder
{
    /**
     * Run the database seeds.
     * User to be excluded
     * 1402
     * 1409
     * 1405
     * 1414
     * 5739
     * 61
     * 1433
     *
     * 2020-12-28 18:30:00
     * 2021-01-18 18:30:00
     *
     * random steps(4000, 8000)
     * random distance(3000, 10000)
     *
     * @return void
     */
    public function run()
    {
        $excludedUsers = [1402, 1409, 1405, 1414, 5739, 61, 1433];
        $companies     = Company::select('id')->where('is_reseller', false)->whereNull('parent_id')->get();
        foreach ($companies as $company) {
            $membersChunk = $company->members()->select('users.id')->whereNotIn('users.id', $excludedUsers)->get()->chunk(500);
            foreach ($membersChunk as $members) {
                foreach ($members as $member) {
                    $chunksEntry   = [];
                    $chunksEntry[] = [
                        'user_id'  => $member->id,
                        'tracker'  => 'googlefit',
                        'steps'    => rand(4000, 8000),
                        'distance' => rand(3000, 10000),
                        'calories' => rand(1000, 2000),
                        'log_date' => '2020-12-28 18:30:00',
                    ];
                    $chunksEntry[] = [
                        'user_id'  => $member->id,
                        'tracker'  => 'googlefit',
                        'steps'    => rand(4000, 8000),
                        'distance' => rand(3000, 10000),
                        'calories' => rand(1000, 2000),
                        'log_date' => '2020-12-29 18:30:00',
                    ];
                    $chunksEntry[] = [
                        'user_id'  => $member->id,
                        'tracker'  => 'googlefit',
                        'steps'    => rand(4000, 8000),
                        'distance' => rand(3000, 10000),
                        'calories' => rand(1000, 2000),
                        'log_date' => '2020-12-30 18:30:00',
                    ];
                    $chunksEntry[] = [
                        'user_id'  => $member->id,
                        'tracker'  => 'googlefit',
                        'steps'    => rand(4000, 8000),
                        'distance' => rand(3000, 10000),
                        'calories' => rand(1000, 2000),
                        'log_date' => '2020-12-31 18:30:00',
                    ];
                    $chunksEntry[] = [
                        'user_id'  => $member->id,
                        'tracker'  => 'googlefit',
                        'steps'    => rand(4000, 8000),
                        'distance' => rand(3000, 10000),
                        'calories' => rand(1000, 2000),
                        'log_date' => '2021-01-01 18:30:00',
                    ];
                    $chunksEntry[] = [
                        'user_id'  => $member->id,
                        'tracker'  => 'googlefit',
                        'steps'    => rand(4000, 8000),
                        'distance' => rand(3000, 10000),
                        'calories' => rand(1000, 2000),
                        'log_date' => '2021-01-02 18:30:00',
                    ];
                    $chunksEntry[] = [
                        'user_id'  => $member->id,
                        'tracker'  => 'googlefit',
                        'steps'    => rand(4000, 8000),
                        'distance' => rand(3000, 10000),
                        'calories' => rand(1000, 2000),
                        'log_date' => '2021-01-03 18:30:00',
                    ];
                    $chunksEntry[] = [
                        'user_id'  => $member->id,
                        'tracker'  => 'googlefit',
                        'steps'    => rand(4000, 8000),
                        'distance' => rand(3000, 10000),
                        'calories' => rand(1000, 2000),
                        'log_date' => '2021-01-04 18:30:00',
                    ];
                    $chunksEntry[] = [
                        'user_id'  => $member->id,
                        'tracker'  => 'googlefit',
                        'steps'    => rand(4000, 8000),
                        'distance' => rand(3000, 10000),
                        'calories' => rand(1000, 2000),
                        'log_date' => '2021-01-05 18:30:00',
                    ];
                    $chunksEntry[] = [
                        'user_id'  => $member->id,
                        'tracker'  => 'googlefit',
                        'steps'    => rand(4000, 8000),
                        'distance' => rand(3000, 10000),
                        'calories' => rand(1000, 2000),
                        'log_date' => '2021-01-06 18:30:00',
                    ];
                    $chunksEntry[] = [
                        'user_id'  => $member->id,
                        'tracker'  => 'googlefit',
                        'steps'    => rand(4000, 8000),
                        'distance' => rand(3000, 10000),
                        'calories' => rand(1000, 2000),
                        'log_date' => '2021-01-07 18:30:00',
                    ];
                    $chunksEntry[] = [
                        'user_id'  => $member->id,
                        'tracker'  => 'googlefit',
                        'steps'    => rand(4000, 8000),
                        'distance' => rand(3000, 10000),
                        'calories' => rand(1000, 2000),
                        'log_date' => '2021-01-08 18:30:00',
                    ];
                    $chunksEntry[] = [
                        'user_id'  => $member->id,
                        'tracker'  => 'googlefit',
                        'steps'    => rand(4000, 8000),
                        'distance' => rand(3000, 10000),
                        'calories' => rand(1000, 2000),
                        'log_date' => '2021-01-09 18:30:00',
                    ];
                    $chunksEntry[] = [
                        'user_id'  => $member->id,
                        'tracker'  => 'googlefit',
                        'steps'    => rand(4000, 8000),
                        'distance' => rand(3000, 10000),
                        'calories' => rand(1000, 2000),
                        'log_date' => '2021-01-10 18:30:00',
                    ];
                    $chunksEntry[] = [
                        'user_id'  => $member->id,
                        'tracker'  => 'googlefit',
                        'steps'    => rand(4000, 8000),
                        'distance' => rand(3000, 10000),
                        'calories' => rand(1000, 2000),
                        'log_date' => '2021-01-11 18:30:00',
                    ];
                    $chunksEntry[] = [
                        'user_id'  => $member->id,
                        'tracker'  => 'googlefit',
                        'steps'    => rand(4000, 8000),
                        'distance' => rand(3000, 10000),
                        'calories' => rand(1000, 2000),
                        'log_date' => '2021-01-12 18:30:00',
                    ];
                    $chunksEntry[] = [
                        'user_id'  => $member->id,
                        'tracker'  => 'googlefit',
                        'steps'    => rand(4000, 8000),
                        'distance' => rand(3000, 10000),
                        'calories' => rand(1000, 2000),
                        'log_date' => '2021-01-13 18:30:00',
                    ];
                    $chunksEntry[] = [
                        'user_id'  => $member->id,
                        'tracker'  => 'googlefit',
                        'steps'    => rand(4000, 8000),
                        'distance' => rand(3000, 10000),
                        'calories' => rand(1000, 2000),
                        'log_date' => '2021-01-14 18:30:00',
                    ];
                    $chunksEntry[] = [
                        'user_id'  => $member->id,
                        'tracker'  => 'googlefit',
                        'steps'    => rand(4000, 8000),
                        'distance' => rand(3000, 10000),
                        'calories' => rand(1000, 2000),
                        'log_date' => '2021-01-15 18:30:00',
                    ];
                    $chunksEntry[] = [
                        'user_id'  => $member->id,
                        'tracker'  => 'googlefit',
                        'steps'    => rand(4000, 8000),
                        'distance' => rand(3000, 10000),
                        'calories' => rand(1000, 2000),
                        'log_date' => '2021-01-16 18:30:00',
                    ];
                    $chunksEntry[] = [
                        'user_id'  => $member->id,
                        'tracker'  => 'googlefit',
                        'steps'    => rand(4000, 8000),
                        'distance' => rand(3000, 10000),
                        'calories' => rand(1000, 2000),
                        'log_date' => '2021-01-17 18:30:00',
                    ];
                    $chunksEntry[] = [
                        'user_id'  => $member->id,
                        'tracker'  => 'googlefit',
                        'steps'    => rand(4000, 8000),
                        'distance' => rand(3000, 10000),
                        'calories' => rand(1000, 2000),
                        'log_date' => '2021-01-18 18:30:00',
                    ];
                    UserStep::insert($chunksEntry);
                }
            }
        }
    }
}
