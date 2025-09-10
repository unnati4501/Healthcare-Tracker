<?php
declare (strict_types = 1);
namespace Database\Seeders;

use App\Http\Traits\DisableForeignKeys;
use App\Http\Traits\TruncateTable;
use App\Models\CpPlan;
use Illuminate\Database\Seeder;

class PlanTableSeeder extends Seeder
{
    use DisableForeignKeys, TruncateTable;
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        try {
            // Disable foreign key checks!
            $this->disableForeignKeys();

            $planArray = [
                [
                    'name'        => 'Digital Therapy with Challenge',
                    'slug'        => 'eap-with-challenge',
                    'group'       => 1,
                    'description' => 'Is the complete plan with access to everything.',
                    'default'     => 1,
                    'status'      => 1,
                    'created_at'  => date('Y-m-d H:i:s'),
                    'updated_at'  => date('Y-m-d H:i:s'),
                ],
                [
                    'name'        => 'Digital Therapy',
                    'slug'        => 'eap',
                    'group'       => 1,
                    'description' => 'Is a basic plan with just EAP and content.',
                    'default'     => 1,
                    'status'      => 1,
                    'created_at'  => date('Y-m-d H:i:s'),
                    'updated_at'  => date('Y-m-d H:i:s'),
                ],
                [
                    'name'        => 'Challenge',
                    'slug'        => 'challenge',
                    'group'       => 1,
                    'description' => 'Is a plan focused on challenges',
                    'default'     => 1,
                    'status'      => 1,
                    'created_at'  => date('Y-m-d H:i:s'),
                    'updated_at'  => date('Y-m-d H:i:s'),
                ],
                [
                    'name'        => 'Standard',
                    'slug'        => 'standard',
                    'group'       => 1,
                    'description' => 'Is most basic plan with just content.',
                    'default'     => 1,
                    'status'      => 1,
                    'created_at'  => date('Y-m-d H:i:s'),
                    'updated_at'  => date('Y-m-d H:i:s'),
                ],
                [
                    'name'        => 'Portal Standard',
                    'slug'        => 'portal-standard',
                    'group'       => 2,
                    'description' => 'Is most basic plan with just content.',
                    'default'     => 1,
                    'status'      => 1,
                    'created_at'  => date('Y-m-d H:i:s'),
                    'updated_at'  => date('Y-m-d H:i:s'),
                ],
                [
                    'name'        => 'Portal Digital Therapy',
                    'slug'        => 'portal-digital-therapy',
                    'group'       => 2,
                    'description' => 'Is most basic plan with just content.',
                    'default'     => 1,
                    'status'      => 1,
                    'created_at'  => date('Y-m-d H:i:s'),
                    'updated_at'  => date('Y-m-d H:i:s'),
                ],
                [
                    'name'        => 'Portal Standard with Digital Therapy',
                    'slug'        => 'portal-standard-with-digital-therapy',
                    'group'       => 2,
                    'description' => 'Is most basic plan with just content.',
                    'default'     => 1,
                    'status'      => 1,
                    'created_at'  => date('Y-m-d H:i:s'),
                    'updated_at'  => date('Y-m-d H:i:s'),
                ],
            ];

            //CpPlan::insert($planArray);
            foreach ($planArray as $value) {
                CpPlan::updateOrCreate(
                    ['slug' => $value['slug']],
                    $value
                );
            }

            // Enable foreign key checks!
            $this->enableForeignKeys();
        } catch (\Illuminate\Database\QueryException $e) {
            $this->command->error("SQL Error: " . $e->getMessage() . "\n");
        }
    }
}
