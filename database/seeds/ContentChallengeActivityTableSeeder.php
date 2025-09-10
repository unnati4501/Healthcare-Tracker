<?php
namespace Database\Seeders;

use App\Http\Traits\DisableForeignKeys;
use App\Http\Traits\TruncateTable;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\ContentChallengeActivity;

class ContentChallengeActivityTableSeeder extends Seeder
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
        $this->truncateMultiple(['content_challenge_activities']);

        $contentChallengeList = \json_decode(
            \file_get_contents(
                __DIR__ . '/data/content_challenge_activities.json'
            ),
            true
        );

        $contentChallenge = [];

        $sort = 1;
        $now  = Carbon::now();
        foreach ($contentChallengeList as $value) {
            $contentChallenge[] = [
                'activity'              => $value['activity'],
                'category_id'           => $value['category_id'],
                'daily_limit'           => $value['daily_limit'],
                'points_per_action'     => $value['points_per_action'],
                'created_at'            => $now,
                'updated_at'            => $now,
            ];
            $sort++;
        }

        try {
            DB::beginTransaction();

            DB::statement('SET FOREIGN_KEY_CHECKS=0;');
            DB::table('content_challenge_activities')->truncate();
            DB::statement('SET FOREIGN_KEY_CHECKS=1;');

            foreach ($contentChallenge as $challenge) {
                ContentChallengeActivity::create($challenge);
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
