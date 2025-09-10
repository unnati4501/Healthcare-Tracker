<?php
declare (strict_types = 1);
namespace Database\Seeders;

use App\Models\Badge;
use Illuminate\Database\Seeder;

class PersonalChallengeBadgeSeeder extends Seeder
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

            Badge::insert([[
                'creator_id'          => 1,
                'type'                => 'challenge',
                'title'               => 'Personal challenge',
                'can_expire'          => 0,
                'target'              => 0,
                'is_default'          => 1,
                'challenge_type_slug' => 'personal',
                'created_at'          => date('Y-m-d H:i:s'),
                'updated_at'          => date('Y-m-d H:i:s'),
            ],

            ]);

            \DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        } catch (\Illuminate\Database\QueryException $e) {
            $this->command->error("SQL Error: " . $e->getMessage() . "\n");
        }
    }
}
