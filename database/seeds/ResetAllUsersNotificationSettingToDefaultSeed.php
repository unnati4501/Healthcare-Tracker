<?php
namespace Database\Seeders;

use App\Http\Traits\TruncateTable;
use App\Models\User;
use Illuminate\Database\Seeder;

class ResetAllUsersNotificationSettingToDefaultSeed extends Seeder
{
    use TruncateTable;

    /**
     * This will update each users to notification settings preference to default as per zevolifesettings.notificationModules as well as add if any user's module entries are missing
     *
     * @return void
     */
    public function run()
    {
        try {
            \DB::beginTransaction();

            // truncate user_notification_settings table before reseting default values
            $this->truncate('user_notification_settings');

            $users = User::all()->chunk(500);
            $users->each(function ($userChunk) {
                $userChunk->each(function ($user) {
                    $updatedModulesArray  = [];
                    $defaultNotifications = ['all' => false] + config('zevolifesettings.notificationModules');
                    foreach ($defaultNotifications as $key => $value) {
                        $updatedModulesArray[] = [
                            'module' => $key,
                            'flag'   => $value,
                        ];
                    }
                    $user->notificationSettings()->createMany($updatedModulesArray);
                });
            });
            \DB::commit();
        } catch (\Exception $exception) {
            \DB::rollBack();
            \Log::critical(__CLASS__, [__FILE__, __LINE__, $exception->getMessage()]);
        }
    }
}
