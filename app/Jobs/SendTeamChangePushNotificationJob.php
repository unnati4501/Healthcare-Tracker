<?php

namespace App\Jobs;

use App\Jobs\SendTeamChangePushNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendTeamChangePushNotificationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * collection of users
     *
     * @var collection
     */
    public $users;

    /**
     * array of extra params
     *
     * @var array
     */
    public $params;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($users, $params)
    {
        $this->users  = $users;
        $this->params = $params;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $newTeam = $this->params['new_team'];
        $this->users->each(function ($user) use ($newTeam) {
            // remove user from all the challenge type group as user is being moved to default team
            removeUserFromChallengeTypeGroups($user, $this->params['company_id']);

            // send team change notification to each users
            \dispatch(new SendTeamChangePushNotification($user, $newTeam->name, $this->params['user_id'], $newTeam->id));
        });
    }
}
