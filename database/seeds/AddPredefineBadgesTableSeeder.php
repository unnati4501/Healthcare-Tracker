<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Badge;

class AddPredefineBadgesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        try {
            \DB::beginTransaction();

            $data = [
                [
                    'creator_id'          => 1,
                    'company_id'          => null,
                    'challenge_target_id' => null,
                    'type'                => 'masterclass',
                    'title'               => 'Masterclass',
                    'description'         => 'masterclass',
                    'can_expire'          => 0,
                    'expires_after'       => 0,
                    'target'              => 0,
                    'uom'                 => null,
                    'is_default'          => 1
                ],
                [
                    'creator_id'          => 1,
                    'company_id'          => null,
                    'challenge_target_id' => null,
                    'type'                => 'daily',
                    'title'               => 'Daily Target Steps',
                    'description'         => 'Daily Target Steps',
                    'can_expire'          => 0,
                    'expires_after'       => 0,
                    'target'              => 0,
                    'uom'                 => null,
                    'is_default'          => 1
                ]
            ];

            Badge::insert($data);

            \DB::commit();
        } catch (\Exception $exception) {
            \DB::rollBack();
            \Log::critical(__CLASS__, [__FILE__, __LINE__, $exception->getMessage()]);
        }
    }
}
