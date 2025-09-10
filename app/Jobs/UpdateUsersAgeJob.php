<?php

namespace App\Jobs;

use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class UpdateUsersAgeJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    private $usersChunk;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($usersChunk)
    {
        $this->queue      = 'notifications';
        $this->usersChunk = $usersChunk;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        try {
            foreach ($this->usersChunk as $user) {
                $profile = $user->profile()->select('id', 'user_id', 'birth_date')->first();
                if (!is_null($profile)) {
                    $profile->age = Carbon::parse($profile->birth_date)->age;
                    $profile->save();
                }
            }
        } catch (\Exception $exception) {
            Log::critical(__CLASS__, [__FILE__, __LINE__, $exception->getMessage()]);
        }
    }
}
