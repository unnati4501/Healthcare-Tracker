<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;

class UpdateAvailabilityStatusForWellbeingspecialistSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $roleId                  = 'wellbeing_specialist'; // Wellbeing Specialist role Id
        User::leftJoin('role_user', function ($join) {
            $join->on('role_user.user_id', '=', 'users.id');
        })
            ->leftJoin('roles', function ($join) {
                $join->on('roles.id', '=', 'role_user.role_id');
            })
            ->leftJoin('health_coach_user', function ($join) {
                $join->on('health_coach_user.user_id', '=', 'users.id');
            })
            ->where('roles.slug', $roleId)
            ->where('availability_status', 0)
            ->update([
                'availability_status' => 1
            ]);
    }
}
