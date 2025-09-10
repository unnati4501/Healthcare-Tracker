<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\EventPresenterSlots;

class SetEventPresenterAvaibility extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $users = User::leftJoin('ws_user', function ($join) {
                $join->on('ws_user.user_id', '=', 'users.id');
            })
            ->leftJoin('role_user', function ($join) {
                $join->on('role_user.user_id', '=', 'users.id');
            })
            ->leftJoin('roles', function ($join) {
                $join->on('roles.id', '=', 'role_user.role_id');
            })
            ->select('users.id')
            ->where('roles.slug','wellbeing_specialist')
            ->where('ws_user.responsibilities', '!=', 1)
            ->where('users.is_blocked', 0)
            ->where('ws_user.is_cronofy', true)
            ->whereNull('users.deleted_at')
            ->get()
            ->toArray();
        $startTime = '09:00:00';
        $endTime   = '17:00:00';
    
        if (sizeOf($users) > 0) {
            foreach ($users as $user) {
                $data = [
                    [
                        'day'               => 'mon',
                        'user_id'           => $user['id'],
                        'start_time'        => $startTime,
                        'end_time'          => $endTime,
                        'created_at'        => date('Y-m-d H:i:s'),
                        'updated_at'        => date('Y-m-d H:i:s'),
                    ],
                    [
                        'day'               => 'tue',
                        'user_id'           => $user['id'],
                        'start_time'        => $startTime,
                        'end_time'          => $endTime,
                        'created_at'        => date('Y-m-d H:i:s'),
                        'updated_at'        => date('Y-m-d H:i:s'),
                    ],
                    [
                        'day'               => 'wed',
                        'user_id'           => $user['id'],
                        'start_time'        => $startTime,
                        'end_time'          => $endTime,
                        'created_at'        => date('Y-m-d H:i:s'),
                        'updated_at'        => date('Y-m-d H:i:s'),
                    ],
                    [
                        'day'               => 'thu',
                        'user_id'           => $user['id'],
                        'start_time'        => $startTime,
                        'end_time'          => $endTime,
                        'created_at'        => date('Y-m-d H:i:s'),
                        'updated_at'        => date('Y-m-d H:i:s'),
                    ],
                    [
                        'day'               => 'fri',
                        'user_id'           => $user['id'],
                        'start_time'        => $startTime,
                        'end_time'          => $endTime,
                        'created_at'        => date('Y-m-d H:i:s'),
                        'updated_at'        => date('Y-m-d H:i:s'),
                    ]
                ];
                EventPresenterSlots::insert($data);
            }
        }
        
    }
}
