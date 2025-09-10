<?php
declare (strict_types = 1);
namespace Database\Seeders;

use App\Models\Badge;
use Illuminate\Database\Seeder;

class ChallengeBadgeSeeder extends Seeder
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
                'title'               => 'individual challenge',
                'can_expire'          => 0,
                'target'              => 0,
                'is_default'          => 1,
                'challenge_type_slug' => 'individual',
                'created_at'          => date('Y-m-d H:i:s'),
                'updated_at'          => date('Y-m-d H:i:s'),
            ], [
                'creator_id'          => 1,
                'type'                => 'challenge',
                'title'               => 'team challenge',
                'can_expire'          => 0,
                'target'              => 0,
                'is_default'          => 1,
                'challenge_type_slug' => 'team',
                'created_at'          => date('Y-m-d H:i:s'),
                'updated_at'          => date('Y-m-d H:i:s'),
            ], [
                'creator_id'          => 1,
                'type'                => 'challenge',
                'title'               => 'company group challenge',
                'can_expire'          => 0,
                'target'              => 0,
                'is_default'          => 1,
                'challenge_type_slug' => 'company_goal',
                'created_at'          => date('Y-m-d H:i:s'),
                'updated_at'          => date('Y-m-d H:i:s'),
            ], [
                'creator_id'          => 1,
                'type'                => 'challenge',
                'title'               => 'intercompany challenge',
                'can_expire'          => 0,
                'target'              => 0,
                'is_default'          => 1,
                'challenge_type_slug' => 'inter_company',
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
