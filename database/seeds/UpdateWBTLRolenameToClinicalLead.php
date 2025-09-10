<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Role;

class UpdateWBTLRolenameToClinicalLead extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        try {
            Role::where('slug', 'wellbeing_team_lead')->update(['name' => 'Clinical Lead']);
        } catch (\Illuminate\Database\QueryException $e) {
            $this->command->error("SQL Error: " . $e->getMessage() . "\n");
        }
    }
}
