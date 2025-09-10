<?php
declare (strict_types = 1);
namespace Database\Seeders;

use App\Http\Traits\DisableForeignKeys;
use App\Http\Traits\TruncateTable;
use App\Models\Role;
use Illuminate\Database\Seeder;

/**
 * Class IndustriesSeeder
 */
class RolesTableSeeder extends Seeder
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

            $roles     = Role::all()->pluck('name', 'slug')->toArray();
            $roleArray = [];

            if (!array_key_exists('super_admin', $roles)) {
                $roleArray[] = [
                    'name'        => 'Super Admin',
                    'slug'        => 'super_admin',
                    'group'       => 'zevo',
                    'description' => 'Manages the whole system',
                    'default'     => 1,
                    'created_at'  => date('Y-m-d H:i:s'),
                    'updated_at'  => date('Y-m-d H:i:s'),
                ];
            }

            if (!array_key_exists('company_admin', $roles)) {
                $roleArray[] = [
                    'name'        => 'Company Admin',
                    'slug'        => 'company_admin',
                    'group'       => 'company',
                    'description' => 'Manages the given company access',
                    'default'     => 1,
                    'created_at'  => date('Y-m-d H:i:s'),
                    'updated_at'  => date('Y-m-d H:i:s'),
                ];
            }

            if (!array_key_exists('user', $roles)) {
                $roleArray[] = [
                    'name'        => 'User',
                    'slug'        => 'user',
                    'group'       => 'company',
                    'description' => 'End user',
                    'default'     => 1,
                    'created_at'  => date('Y-m-d H:i:s'),
                    'updated_at'  => date('Y-m-d H:i:s'),
                ];
            }

            if (!array_key_exists('reseller_super_admin', $roles)) {
                $roleArray[] = [
                    'name'        => 'Reseller Super Admin',
                    'slug'        => 'reseller_super_admin',
                    'group'       => 'reseller',
                    'description' => 'Manages the reseller company and its child companies',
                    'default'     => 1,
                    'created_at'  => date('Y-m-d H:i:s'),
                    'updated_at'  => date('Y-m-d H:i:s'),
                ];
            }

            if (!array_key_exists('reseller_company_admin', $roles)) {
                $roleArray[] = [
                    'name'        => 'Reseller Company Admin',
                    'slug'        => 'reseller_company_admin',
                    'group'       => 'reseller',
                    'description' => 'Manages the reseller child companies',
                    'default'     => 1,
                    'created_at'  => date('Y-m-d H:i:s'),
                    'updated_at'  => date('Y-m-d H:i:s'),
                ];
            }

            if (!array_key_exists('health_coach', $roles)) {
                $roleArray[] = [
                    'name'        => 'Wellbeing Consultant',
                    'slug'        => 'health_coach',
                    'group'       => 'zevo',
                    'description' => 'Wellbeing Consultant is a kind of end user, he/she can be speaker of the event.',
                    'default'     => 1,
                    'created_at'  => date('Y-m-d H:i:s'),
                    'updated_at'  => date('Y-m-d H:i:s'),
                ];
            } else {
                // HACK to update "Health Coach" with "Wellbeing Specialist"
                // HACK to update "Wellbeing Specialist" with "Wellbeing Consultant"
                if ($roles['health_coach'] == "Wellbeing Specialist") {
                    Role::where('slug', 'health_coach')
                        ->update([
                            'name'        => 'Wellbeing Consultant',
                            'description' => 'Wellbeing Consultant is a kind of end user, he/she can be speaker of the event.',
                        ]);
                }
            }

            if (!array_key_exists('counsellor', $roles)) {
                $roleArray[] = [
                    'name'        => 'Counsellor',
                    'slug'        => 'counsellor',
                    'group'       => 'zevo',
                    'description' => 'Counsellor is a kind of end user, he/she will act as a therapist for the user.',
                    'default'     => 1,
                    'created_at'  => date('Y-m-d H:i:s'),
                    'updated_at'  => date('Y-m-d H:i:s'),
                ];
            }

            if (!array_key_exists('wellbeing_specialist', $roles)) {
                $roleArray[] = [
                    'name'        => 'Wellbeing Specialist',
                    'slug'        => 'wellbeing_specialist',
                    'group'       => 'zevo',
                    'description' => 'Wellbeing Specialist is a kind of end user, he/she will act as a nylas counsellor for the user.',
                    'default'     => 1,
                    'created_at'  => date('Y-m-d H:i:s'),
                    'updated_at'  => date('Y-m-d H:i:s'),
                ];
            }

            if (!array_key_exists('wellbeing_team_lead', $roles)) {
                $roleArray[] = [
                    'name'        => 'Wellbeing Team Lead',
                    'slug'        => 'wellbeing_team_lead',
                    'group'       => 'zevo',
                    'description' => 'Wellbeing Team Lead is a kind of end user, he/she will act as a nylas wellbeing team lead for the user.',
                    'default'     => 1,
                    'created_at'  => date('Y-m-d H:i:s'),
                    'updated_at'  => date('Y-m-d H:i:s'),
                ];
            }

            Role::insert($roleArray);

            // Enable foreign key checks!
            $this->enableForeignKeys();
        } catch (\Illuminate\Database\QueryException $e) {
            $this->command->error("SQL Error: " . $e->getMessage() . "\n");
        }
    }
}
