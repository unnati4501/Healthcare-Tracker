<?php
declare (strict_types = 1);
namespace Database\Seeders;

use App\Models\ChallengeCategory;
use Illuminate\Database\Seeder;

/**
 * Class IndustriesSeeder
 */
class ChallengeCategoryTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        try {
            \DB::statement('SET FOREIGN_KEY_CHECKS=0;');
            \DB::table('challenge_categories')->truncate();

            ChallengeCategory::insert([
                [
                    'name'        => 'First to reach',
                    'short_name'  => 'first_to_reach',
                    'is_excluded' => 0,
                    'created_at'  => date('Y-m-d H:i:s'),
                    'updated_at'  => date('Y-m-d H:i:s'),
                ],
                [
                    'name'        => 'Most',
                    'short_name'  => 'most',
                    'is_excluded' => 0,
                    'created_at'  => date('Y-m-d H:i:s'),
                    'updated_at'  => date('Y-m-d H:i:s'),
                ],
                [
                    'name'        => 'Streak',
                    'short_name'  => 'streak',
                    'is_excluded' => 0,
                    'created_at'  => date('Y-m-d H:i:s'),
                    'updated_at'  => date('Y-m-d H:i:s'),
                ],
                [
                    'name'        => 'Combined (FTR)',
                    'short_name'  => 'combined',
                    'is_excluded' => 0,
                    'created_at'  => date('Y-m-d H:i:s'),
                    'updated_at'  => date('Y-m-d H:i:s'),
                ],
                [
                    'name'        => 'Combined (Most)',
                    'short_name'  => 'combined_most',
                    'is_excluded' => 0,
                    'created_at'  => date('Y-m-d H:i:s'),
                    'updated_at'  => date('Y-m-d H:i:s'),
                ],
                // [
                //     'name'        => 'Fastest',
                //     'short_name'  => 'fastest',
                //     'is_excluded' => 0,
                //     'created_at'  => date('Y-m-d H:i:s'),
                //     'updated_at'  => date('Y-m-d H:i:s'),
                // ],
            ]);

            \DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        } catch (\Illuminate\Database\QueryException $e) {
            $this->command->error("SQL Error: " . $e->getMessage() . "\n");
        }
    }
}
