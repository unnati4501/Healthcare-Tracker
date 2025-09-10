<?php
namespace Database\Seeders;

use App\Events\InviteExistingWellbeingConsultantEvent;
use App\Models\EventPresenters;
use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * Class InviteExistingWellbeingConsultant
 */
class InviteExistingWellbeingConsultant extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Get Wellbeing consultant role users
        $roleId                  = 'health_coach'; // Wellbeing consultant role Id
        $wellbeingConsultantUser = User::leftJoin('role_user', function ($join) {
            $join->on('role_user.user_id', '=', 'users.id');
        })
            ->leftJoin('roles', function ($join) {
                $join->on('roles.id', '=', 'role_user.role_id');
            })
            ->leftJoin('health_coach_user', function ($join) {
                $join->on('health_coach_user.user_id', '=', 'users.id');
            })
            ->where('roles.slug', $roleId)
            ->where(function ($q) {
                $q->whereNull('health_coach_user.is_cronofy')
                    ->orWhere('health_coach_user.is_cronofy', false);
            })
            ->select('users.id', \DB::raw("users.first_name AS name"), 'users.email')
            ->get()
            ->toArray();

        if (!empty($wellbeingConsultantUser)) {
            $wcIds = array_column($wellbeingConsultantUser, 'id');
            EventPresenters::whereIn('user_id', $wcIds)->delete();
            foreach ($wellbeingConsultantUser as $value) {
                event(new InviteExistingWellbeingConsultantEvent([
                    'name'  => $value['name'],
                    'email' => $value['email'],
                    'id'    => $value['id'],
                ]));
            }
        }
    }
}
