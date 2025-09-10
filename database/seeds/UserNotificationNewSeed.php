<?php
namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class UserNotificationNewSeed extends Seeder
{
    /**
     * This will add feed, course as a notificainot category for each users and set default value false
     *
     * @return void
     */
    public function run()
    {
        try {
            \DB::beginTransaction();
            $users = User::all()->chunk(500);
            $users->each(function ($userChunk) {
                $userChunk->each(function ($user) {
                    $user->notificationSettings()->createMany([[
                        'module' => 'feeds',
                        'flag'   => false,
                    ], [
                        'module' => 'courses',
                        'flag'   => false,
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
