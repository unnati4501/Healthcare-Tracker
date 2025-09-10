<?php

namespace App\Jobs;

use App\Jobs\SendGeneralPushNotification;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendSetProfilePictureReminderPushNotification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Collection of users
     *
     * @var collection
     */
    protected $user;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($users)
    {
        $this->user = $users;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {

        $appTimeZone = config('app.timezone');
        $now         = now($appTimeZone);
        $date        = $now->setTime(9, 59, 0, 0)->toDateTimeString();
        $userIds     = $this->user->pluck('id')->toArray();

        $this->user->each(function ($user) use ($date, $appTimeZone) {
            $notificaionSchDate = Carbon::parse($date, $user->timezone)->setTimezone($appTimeZone)->todatetimeString();

            // send push notification to user for set profile picture
            \dispatch(new SendGeneralPushNotification($user, 'set-profile-picture', [
                'type'         => 'Manual',
                'scheduled_at' => $notificaionSchDate,
                'push'         => true,
            ]));
        });

        User::whereIn('id', $userIds)->update(['set_profile_picture_reminder_at' => now($appTimeZone)->toDateTimeString()]);
    }
}
