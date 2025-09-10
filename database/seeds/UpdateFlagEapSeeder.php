<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\EAP;
use App\Models\User;
use App\Models\Company;

class UpdateFlagEapSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        try {
            $eapList = EAP::all()->chunk(500);
            $eapList->each(function ($eapListChunk) {
                $eapListChunk->each(function ($eap) {
                    if ($eap['creator_id'] != null) {
                        $user = User::find($eap['creator_id']);
                        $role = getUserRole($user);
                        if ($eap['company_id'] != null) {
                            $company = Company::find($eap['company_id']);
                        }
                        if (!empty($role) ||  !empty($company)) {
                            $roleGroup = $role->group;
                            if ($roleGroup == 'company' || ($roleGroup == 'reseller' && $company->parent_id != null)) {
                                if (empty($eap['locations']) && empty($eap['departments'])) {
                                    $eap->update(['is_rca' => 0]);
                                } else {
                                    $eap->update(['is_rca' => 1]);
                                }
                            }
                        }
                    }
                });
            });
        } catch (QueryException $e) {
            $this->command->error("SQL Error: " . $e->getMessage() . "\n");
        }
    }
}
