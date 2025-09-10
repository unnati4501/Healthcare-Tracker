<?php
namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class AddEventTypeNotificationForExistingUsers extends Seeder
{
    /**
     * This will add event as a notification category for each users and set default value as true
     *
     * @return void
     */
    public function run()
    {
        try {
            \DB::beginTransaction();
            $users = User::all()->chunk(500);
            $users->each(function ($userChunk, $key) {
                $userChunk->each(function ($user) {
                    $user->notificationSettings()->createMany([[
                        'module' => 'events',
                        'flag'   => true,
                    ]]);
                });
            });
            \DB::commit();
        } catch (\Exception $exception) {
            \DB::rollBack();
            \Log::critical(__CLASS__, [__FILE__, __LINE__, $exception->getMessage()]);
        }
    }
}
