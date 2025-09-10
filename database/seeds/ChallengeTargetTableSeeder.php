<?php
declare (strict_types = 1);
namespace Database\Seeders;

use App\Models\ChallengeTarget;
use Illuminate\Database\Seeder;

/**
 * Class IndustriesSeeder
 */
class ChallengeTargetTableSeeder extends Seeder
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
            \DB::table('challenge_targets')->truncate();

            ChallengeTarget::insert([[
                'name'              => 'Steps',
                'short_name'        => 'steps',
                'is_excluded'       => 0,
                'created_at'        => date('Y-m-d H:i:s'),
                'updated_at'        => date('Y-m-d H:i:s'),
            ]
            ,[
                'name'              => 'Distance',
                'short_name'        => 'distance',
                'is_excluded'       => 0,
                'created_at'        => date('Y-m-d H:i:s'),
                'updated_at'        => date('Y-m-d H:i:s'),
            ]
            ,[
                'name'              => 'Calories',
                'short_name'        => 'calories',
                'is_excluded'       => 1,
                'created_at'        => date('Y-m-d H:i:s'),
                'updated_at'        => date('Y-m-d H:i:s'),
            ]
            ,[
                'name'              => 'Exercises',
                'short_name'        => 'exercises',
                'is_excluded'       => 0,
                'created_at'        => date('Y-m-d H:i:s'),
                'updated_at'        => date('Y-m-d H:i:s'),
            ],
            [
                'name'              => 'Meditations',
                'short_name'        => 'meditations',
                'is_excluded'       => 0,
                'created_at'        => date('Y-m-d H:i:s'),
                'updated_at'        => date('Y-m-d H:i:s'),
            ]
            ,[
                'name'              => 'Content',
                'short_name'        => 'content',
                'is_excluded'       => 0,
                'created_at'        => date('Y-m-d H:i:s'),
                'updated_at'        => date('Y-m-d H:i:s'),
            ]
            
            ]);

            \DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        } catch (\Illuminate\Database\QueryException $e) {
            $this->command->error("SQL Error: " . $e->getMessage() . "\n");
        }
    }
}
